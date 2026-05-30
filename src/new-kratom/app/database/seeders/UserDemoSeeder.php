<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data for the showcase account (parabellum.koval@gmail.com): six orders
 * spread across the order lifecycle, a saved Czech address, and a handful of
 * verified-purchase reviews. Re-runnable: clears existing demo orders/reviews
 * for the same user before inserting fresh ones.
 */
class UserDemoSeeder extends Seeder
{
    private const EMAIL = 'parabellum.koval@gmail.com';

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Andrej Koval',
                'password' => Hash::make('password'),
                'phone' => '+420 777 111 222',
                'email_verified_at' => now(),
                'marketing_consent' => true,
                'locale' => 'cs',
            ],
        );

        $this->seedAddress($user);
        $this->seedOrders($user);
        $this->seedReviews($user);
    }

    private function seedAddress(User $user): void
    {
        $praha = City::where('name', 'Praha')->orderByDesc('population')->first();
        if (! $praha) {
            return;
        }

        $user->addresses()->updateOrCreate(
            ['user_id' => $user->id, 'city_id' => $praha->id, 'street' => 'Křižíkova 27/6'],
            ['phone' => $user->phone, 'is_default' => true],
        );
    }

    private function seedOrders(User $user): void
    {
        Order::where('user_id', $user->id)->delete();

        $variants = ProductVariant::with('product')->limit(8)->get();
        if ($variants->isEmpty()) {
            return;
        }

        $scenarios = [
            ['status' => 'delivered',  'payment' => 'paid',    'days' => 92, 'item_count' => 2],
            ['status' => 'delivered',  'payment' => 'paid',    'days' => 60, 'item_count' => 1],
            ['status' => 'shipped',    'payment' => 'paid',    'days' => 6,  'item_count' => 3],
            ['status' => 'packed',     'payment' => 'paid',    'days' => 3,  'item_count' => 2],
            ['status' => 'received',   'payment' => 'unpaid',  'days' => 1,  'item_count' => 1],
            ['status' => 'cancelled',  'payment' => 'refunded', 'days' => 30, 'item_count' => 2],
        ];

        foreach ($scenarios as $i => $s) {
            $this->buildOrder($user, $variants, $s, $i + 1);
        }
    }

    private function buildOrder(User $user, \Illuminate\Support\Collection $variants, array $s, int $seq): Order
    {
        $createdAt = Carbon::now()->subDays($s['days'])->subHours(3 + $seq);

        $picks = $variants->shuffle()->take($s['item_count']);
        $subtotal = 0;
        $items = [];

        foreach ($picks as $v) {
            $qty = random_int(1, 2);
            $line = $v->price * $qty;
            $subtotal += $line;
            $items[] = [
                'variant' => $v,
                'qty' => $qty,
                'line_total' => $line,
            ];
        }

        $shipping = $subtotal >= 1500 ? 0 : 99;
        $total = $subtotal + $shipping;

        $order = Order::create([
            'user_id' => $user->id,
            'public_id' => sprintf('VZ-%s-%04d', $createdAt->format('Y'), 9000 + $seq),
            'status' => $s['status'],
            'email' => $user->email,
            'phone' => $user->phone ?? '+420 777 111 222',
            'first_name' => 'Andrej',
            'last_name' => 'Koval',
            'street' => 'Křižíkova 27/6',
            'city' => 'Praha',
            'zip' => '186 00',
            'country' => 'CZ',
            'delivery_method' => $s['status'] === 'cancelled' ? 'messanger' : ($seq % 2 === 0 ? 'messanger_express' : 'pickup_praha'),
            'payment_method' => $seq % 2 === 0 ? 'qr' : 'bank_transfer',
            'payment_status' => $s['payment'],
            'subtotal' => $subtotal,
            'discount' => 0,
            'shipping' => $shipping,
            'total' => $total,
            'items_count' => array_sum(array_column($items, 'qty')),
            'locale' => 'cs',
            'marketing_consent' => true,
            'note' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        foreach ($items as $it) {
            /** @var ProductVariant $v */
            $v = $it['variant'];
            OrderItem::create([
                'order_id' => $order->id,
                'product_slug' => $v->product->slug,
                'product_name' => is_array($v->product->name)
                    ? ($v->product->name['cs'] ?? reset($v->product->name))
                    : $v->product->name,
                'size' => $v->size,
                'unit' => $v->unit,
                'qty' => $it['qty'],
                'unit_price' => $v->price,
                'line_total' => $it['line_total'],
                'snapshot' => [
                    'variant_id' => $v->id,
                    'price_at_purchase' => $v->price,
                ],
            ]);
        }

        $this->seedHistory($order, $s['status'], $createdAt);

        return $order;
    }

    private function seedHistory(Order $order, string $finalStatus, Carbon $createdAt): void
    {
        $chain = match ($finalStatus) {
            'delivered' => ['received', 'paid', 'packed', 'shipped', 'delivered'],
            'shipped'   => ['received', 'paid', 'packed', 'shipped'],
            'packed'    => ['received', 'paid', 'packed'],
            'received'  => ['received'],
            'cancelled' => ['received', 'cancelled'],
            default     => [$finalStatus],
        };

        $previous = null;
        $t = $createdAt->copy();

        foreach ($chain as $status) {
            $order->history()->create([
                'from_status' => $previous,
                'to_status'   => $status,
                'created_at'  => $t->copy(),
                'updated_at'  => $t->copy(),
            ]);
            $previous = $status;
            $t->addHours(random_int(4, 20));
        }
    }

    private function seedReviews(User $user): void
    {
        ProductReview::where('user_id', $user->id)->delete();

        $reviews = [
            ['slug' => 'cervena-maeng-da', 'rating' => 5, 'days' => 80,
             'body' => 'Druhý nákup u Vivadzen. Šarže opět odpovídá COA — to je u nás klíčové. Doručení do Prahy 2. den.'],
            ['slug' => 'zelena-maeng-da', 'rating' => 5, 'days' => 55,
             'body' => 'Stabilní mletí, balení precizní. Express doručení v Praze fungovalo dokonale.'],
            ['slug' => 'bila-maeng-da', 'rating' => 4, 'days' => 28,
             'body' => 'Velmi dobrá bílá, vyvážený profil. Strhávám hvězdičku jen za drobné rozdíly mezi šaržemi.'],
            ['slug' => 'zelena-sumatra', 'rating' => 5, 'days' => 14,
             'body' => 'Konzistentní kvalita, COA otevřené. Doporučuji.'],
        ];

        foreach ($reviews as $r) {
            $product = Product::where('slug', $r['slug'])->first();
            if (! $product) {
                continue;
            }
            ProductReview::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'author_name' => $user->name,
                'author_email' => $user->email,
                'rating' => $r['rating'],
                'body' => $r['body'],
                'verified_purchase' => true,
                'helpful_count' => random_int(0, 5),
                'status' => 'approved',
                'published_at' => Carbon::now()->subDays($r['days']),
                'created_at' => Carbon::now()->subDays($r['days']),
                'updated_at' => Carbon::now()->subDays($r['days']),
            ]);
        }
    }
}
