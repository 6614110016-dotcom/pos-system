<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * โมดูลการขาย (Sale) + โมดูลจ่ายเงิน (Payment)
 * สร้างบิลขายย้อนหลัง 30 วัน เพื่อให้แดชบอร์ดและรายงานมีข้อมูลให้ดูทันที
 */
class DemoSaleSeeder extends Seeder
{
    public function run(): void
    {
        if (Sale::exists()) {
            $this->command->warn('มีข้อมูลการขายอยู่แล้ว ข้ามการสร้างข้อมูลตัวอย่าง');

            return;
        }

        $cashiers  = User::whereIn('role', ['cashier', 'manager'])->get();
        $members   = Member::all();
        $vatRate   = Setting::vatRate();
        $pointRate = Setting::pointRate();

        // เติมสต็อกก่อน เพื่อให้ขายย้อนหลังได้โดยสต็อกไม่ติดลบ
        foreach (Product::all() as $p) {
            $p->adjustStock(200, 'in', 'DEMO-STOCK', 'เติมสต็อกสำหรับข้อมูลตัวอย่าง', 1);
        }

        $billCounter = [];

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $date  = now()->subDays($daysAgo);
            $bills = rand(2, 7);   // จำนวนบิลต่อวัน

            for ($b = 0; $b < $bills; $b++) {
                $soldAt = $date->copy()
                    ->setTime(rand(8, 20), rand(0, 59), rand(0, 59));

                $cashier = $cashiers->random();
                $member  = rand(1, 10) <= 5 ? $members->random() : null;   // 50% เป็นสมาชิก

                $products = Product::where('stock_qty', '>', 5)
                    ->inRandomOrder()
                    ->limit(rand(1, 5))
                    ->get();

                if ($products->isEmpty()) {
                    continue;
                }

                DB::transaction(function () use (
                    $products, $member, $cashier, $soldAt, $vatRate, $pointRate, &$billCounter
                ) {
                    $key   = $soldAt->format('Ymd');
                    $seq   = ($billCounter[$key] ?? 0) + 1;
                    $billCounter[$key] = $seq;
                    $saleNo = 'INV-'.$key.'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

                    $lines    = [];
                    $subtotal = 0;

                    foreach ($products as $p) {
                        $qty      = rand(1, 4);
                        $lineTotal = $p->price * $qty;
                        $subtotal += $lineTotal;

                        $lines[] = [
                            'product'  => $p,
                            'qty'      => $qty,
                            'subtotal' => $lineTotal,
                        ];
                    }

                    $memberDiscount = $member ? round($subtotal * $member->discount_rate / 100, 2) : 0;
                    $billDiscount   = rand(1, 10) === 1 ? min(20, round($subtotal * 0.05, 2)) : 0;
                    $total          = round($subtotal - $memberDiscount - $billDiscount, 2);
                    $vat            = round($total - ($total * 100 / (100 + $vatRate)), 2);
                    $points         = $member ? (int) floor($total / $pointRate) : 0;

                    $sale = Sale::create([
                        'sale_no'         => $saleNo,
                        'member_id'       => $member?->id,
                        'user_id'         => $cashier->id,
                        'subtotal'        => $subtotal,
                        'discount'        => $billDiscount,
                        'member_discount' => $memberDiscount,
                        'vat'             => $vat,
                        'total'           => $total,
                        'points_earned'   => $points,
                        'status'          => 'paid',
                        'sold_at'         => $soldAt,
                        'created_at'      => $soldAt,
                        'updated_at'      => $soldAt,
                    ]);

                    foreach ($lines as $line) {
                        /** @var Product $p */
                        $p = $line['product'];

                        $sale->items()->create([
                            'product_id'   => $p->id,
                            'product_code' => $p->product_code,
                            'product_name' => $p->name,
                            'price'        => $p->price,
                            'cost_price'   => $p->cost_price,
                            'qty'          => $line['qty'],
                            'discount'     => 0,
                            'subtotal'     => $line['subtotal'],
                            'created_at'   => $soldAt,
                            'updated_at'   => $soldAt,
                        ]);

                        $p->adjustStock(-$line['qty'], 'out', $saleNo, 'ตัดสต็อกจากการขาย', $cashier->id);
                    }

                    // ชำระเงิน (70% เงินสด)
                    $method   = rand(1, 10) <= 7 ? 'cash' : collect(['transfer', 'promptpay', 'credit_card'])->random();
                    $received = $method === 'cash' ? ceil($total / 20) * 20 : $total;

                    Payment::create([
                        'payment_no'    => 'PAY-'.$key.'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
                        'sale_id'       => $sale->id,
                        'method'        => $method,
                        'amount'        => $total,
                        'received'      => $received,
                        'change_amount' => $method === 'cash' ? round($received - $total, 2) : 0,
                        'status'        => 'success',
                        'user_id'       => $cashier->id,
                        'paid_at'       => $soldAt,
                        'created_at'    => $soldAt,
                        'updated_at'    => $soldAt,
                    ]);

                    if ($member) {
                        $member->increment('points', $points);
                        $member->increment('total_spent', $total);
                    }
                });
            }
        }

        // ปรับระดับสมาชิกตามยอดซื้อสะสมที่เกิดขึ้นจริง
        Member::all()->each(fn ($m) => $m->refreshTier());

        $this->command->info('สร้างบิลขายตัวอย่างแล้ว '.Sale::count().' บิล');
    }
}
