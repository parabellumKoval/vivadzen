<?php

namespace App\Http\Controllers;

use App\Jobs\CreateOrderJob;
use App\Support\Cart;
use App\Support\Locale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    private function ensureCart()
    {
        $snapshot = Cart::snapshot();
        if ($snapshot['count'] === 0) {
            return redirect(Locale::url('/kosik'));
        }
        return $snapshot;
    }

    public function delivery()
    {
        $cart = $this->ensureCart();
        if (! is_array($cart)) return $cart;
        return view('pages.checkout.delivery', [
            'cart' => $cart,
            'step' => 1,
            'checkout' => Cart::checkout(),
        ]);
    }

    public function saveDelivery(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string|max:64',
            'first_name' => 'required|string|max:64',
            'last_name' => 'required|string|max:64',
            'street' => 'required|string|max:128',
            'city' => 'required|string|max:64',
            'zip' => 'required|string|max:16',
            'country' => 'nullable|string|max:64',
            'delivery_method' => 'required|in:courier,express,pickup,zasilkovna',
            'marketing_consent' => 'nullable|boolean',
        ]);
        Cart::setCheckout(['delivery' => $data]);
        return redirect(Locale::url('/pokladna/platba'));
    }

    public function payment()
    {
        $cart = $this->ensureCart();
        if (! is_array($cart)) return $cart;

        $checkout = Cart::checkout();
        if (empty($checkout['delivery'])) {
            return redirect(Locale::url('/pokladna'));
        }

        return view('pages.checkout.payment', [
            'cart' => $cart,
            'step' => 2,
            'checkout' => $checkout,
        ]);
    }

    public function savePayment(Request $request)
    {
        $data = $request->validate([
            'payment_method' => 'required|in:card,qr,bank,cod,store',
        ]);
        Cart::setCheckout(['payment' => $data]);
        return redirect(Locale::url('/pokladna/potvrzeni'));
    }

    public function review()
    {
        $cart = $this->ensureCart();
        if (! is_array($cart)) return $cart;

        $checkout = Cart::checkout();
        if (empty($checkout['delivery']) || empty($checkout['payment'])) {
            return redirect(Locale::url('/pokladna'));
        }

        return view('pages.checkout.review', [
            'cart' => $cart,
            'step' => 3,
            'checkout' => $checkout,
        ]);
    }

    /**
     * Финальное оформление: мы НЕ пишем в MySQL отсюда (фронт не должен).
     * Снапшот корзины кладётся в Redis, заказ диспатчится в очередь,
     * страница успеха показывается мгновенно с публичным ID.
     */
    public function submit(Request $request)
    {
        $request->validate([
            'consent_age' => 'required|accepted',
            'consent_terms' => 'required|accepted',
            'consent_safety' => 'required|accepted',
        ]);

        $cart = Cart::snapshot();
        if ($cart['count'] === 0) {
            return redirect(Locale::url('/kosik'));
        }

        $checkout = Cart::checkout();
        if (empty($checkout['delivery']) || empty($checkout['payment'])) {
            return redirect(Locale::url('/pokladna'));
        }

        $publicId = 'VZ-'.now()->format('Y').'-'.str_pad(
            (string) random_int(1000, 9999),
            4, '0', STR_PAD_LEFT
        );

        // Pending-статус сразу видим во фронте до того, как worker отработает
        Redis::setex("nk:v1:order:pending:{$publicId}", 3600, json_encode([
            'status' => 'pending',
            'public_id' => $publicId,
            'created_at' => now()->toIso8601String(),
        ]));

        CreateOrderJob::dispatch(
            publicId: $publicId,
            cart: $cart,
            delivery: $checkout['delivery'],
            payment: $checkout['payment'],
            locale: app()->getLocale(),
            ip: $request->ip(),
        )->onQueue('orders');

        Session::put('last_order_id', $publicId);
        Session::put('last_order', [
            'public_id' => $publicId,
            'cart' => $cart,
            'delivery' => $checkout['delivery'],
            'payment' => $checkout['payment'],
        ]);
        Cart::clear();

        return redirect(Locale::url('/objednavka/uspech?id='.urlencode($publicId)));
    }

    public function success(Request $request)
    {
        $publicId = $request->query('id') ?? Session::get('last_order_id');

        // Берём актуальный статус из Redis — заказ может быть pending пока
        // не отработал worker.
        $statusRaw = $publicId ? Redis::get("nk:v1:order:pending:{$publicId}") : null;
        $status = $statusRaw ? json_decode($statusRaw, true) : ['status' => 'unknown'];

        $order = Session::get('last_order');
        if ($order && ($order['public_id'] ?? null) !== $publicId) {
            $order = null;
        }

        return view('pages.order.success', [
            'orderId' => $publicId,
            'orderStatus' => $status,
            'order' => $order,
        ]);
    }

    public function error()
    {
        return view('pages.order.error');
    }

    /**
     * Polling-endpoint для фронта: страница успеха раз в 2 секунды
     * опрашивает, пока статус не станет "received".
     */
    public function status(string $publicId): JsonResponse
    {
        $raw = Redis::get("nk:v1:order:pending:{$publicId}");
        $payload = $raw ? json_decode($raw, true) : ['status' => 'unknown'];
        return response()->json($payload);
    }
}
