<?php

namespace App\Http\Controllers;

use App\Support\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('pages.cart.index', [
            'cart' => Cart::snapshot(),
        ]);
    }

    public function data(): JsonResponse
    {
        return response()->json(Cart::snapshot());
    }

    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'slug' => 'required|string',
            'size' => 'required|integer|in:25,50',
            'qty' => 'nullable|integer|min:1|max:99',
        ]);

        $result = Cart::add($data['slug'], (int) $data['size'], (int) ($data['qty'] ?? 1));

        if (isset($result['error'])) {
            return response()->json(['ok' => false, 'error' => $result['error']], 422);
        }

        return response()->json([
            'ok' => true,
            'cart' => Cart::snapshot(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => 'required|string',
            'qty' => 'nullable|integer|min:0|max:99',
            'size' => 'nullable|integer|in:25,50',
        ]);
        Cart::update($data['key'], $data['qty'] ?? null, $data['size'] ?? null);
        return response()->json(['ok' => true, 'cart' => Cart::snapshot()]);
    }

    public function remove(Request $request): JsonResponse
    {
        $data = $request->validate(['key' => 'required|string']);
        Cart::remove($data['key']);
        return response()->json(['ok' => true, 'cart' => Cart::snapshot()]);
    }

    public function promo(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:32',
            'action' => 'nullable|string|in:apply,remove',
        ]);

        $action = $data['action'] ?? 'apply';

        if ($action === 'remove') {
            Cart::removePromo();
            return response()->json(['ok' => true, 'cart' => Cart::snapshot()]);
        }

        $code = $data['code'] ?? '';
        $applied = Cart::applyPromo($code);

        return response()->json([
            'ok' => $applied,
            'cart' => Cart::snapshot(),
            'message' => $applied ? 'applied' : 'invalid_code',
        ], $applied ? 200 : 422);
    }
}
