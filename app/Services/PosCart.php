<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Session;

/**
 * โมดูลการขาย (Sale) - ตะกร้าสินค้าหน้าจอ POS
 * เก็บข้อมูลตะกร้าไว้ใน Session ของพนักงานที่กำลังขาย
 */
class PosCart
{
    protected const KEY_ITEMS    = 'pos.items';
    protected const KEY_MEMBER   = 'pos.member_id';
    protected const KEY_DISCOUNT = 'pos.discount';

    /* ---------------- อ่านข้อมูล ---------------- */

    public function items(): array
    {
        return Session::get(self::KEY_ITEMS, []);
    }

    public function count(): int
    {
        return array_sum(array_column($this->items(), 'qty'));
    }

    public function isEmpty(): bool
    {
        return empty($this->items());
    }

    public function member(): ?Member
    {
        $id = Session::get(self::KEY_MEMBER);

        return $id ? Member::find($id) : null;
    }

    public function billDiscount(): float
    {
        return (float) Session::get(self::KEY_DISCOUNT, 0);
    }

    /* ---------------- แก้ไขตะกร้า ---------------- */

    public function add(Product $product, int $qty = 1): array
    {
        $items = $this->items();
        $key   = (string) $product->id;

        $currentQty = $items[$key]['qty'] ?? 0;
        $newQty     = $currentQty + $qty;

        if ($newQty > $product->stock_qty) {
            return [
                'ok'      => false,
                'message' => "สินค้า \"{$product->name}\" มีคงเหลือเพียง {$product->stock_qty} {$product->unit}",
            ];
        }

        $items[$key] = [
            'product_id'   => $product->id,
            'product_code' => $product->product_code,
            'name'         => $product->name,
            'unit'         => $product->unit,
            'price'        => (float) $product->price,
            'cost_price'   => (float) $product->cost_price,
            'qty'          => $newQty,
            'discount'     => $items[$key]['discount'] ?? 0,
            'stock'        => $product->stock_qty,
        ];

        Session::put(self::KEY_ITEMS, $items);

        return ['ok' => true, 'message' => "เพิ่ม \"{$product->name}\" ลงตะกร้าแล้ว"];
    }

    public function updateQty(int $productId, int $qty): array
    {
        $items = $this->items();
        $key   = (string) $productId;

        if (! isset($items[$key])) {
            return ['ok' => false, 'message' => 'ไม่พบสินค้าในตะกร้า'];
        }

        if ($qty <= 0) {
            return $this->remove($productId);
        }

        $product = Product::find($productId);

        if ($product && $qty > $product->stock_qty) {
            return [
                'ok'      => false,
                'message' => "สินค้ามีคงเหลือเพียง {$product->stock_qty} {$product->unit}",
            ];
        }

        $items[$key]['qty'] = $qty;
        Session::put(self::KEY_ITEMS, $items);

        return ['ok' => true, 'message' => 'ปรับจำนวนเรียบร้อย'];
    }

    public function updateItemDiscount(int $productId, float $discount): array
    {
        $items = $this->items();
        $key   = (string) $productId;

        if (! isset($items[$key])) {
            return ['ok' => false, 'message' => 'ไม่พบสินค้าในตะกร้า'];
        }

        $max = $items[$key]['price'] * $items[$key]['qty'];
        $items[$key]['discount'] = max(0, min($discount, $max));

        Session::put(self::KEY_ITEMS, $items);

        return ['ok' => true, 'message' => 'บันทึกส่วนลดรายการแล้ว'];
    }

    public function remove(int $productId): array
    {
        $items = $this->items();
        unset($items[(string) $productId]);
        Session::put(self::KEY_ITEMS, $items);

        return ['ok' => true, 'message' => 'นำสินค้าออกจากตะกร้าแล้ว'];
    }

    public function setMember(?int $memberId): void
    {
        Session::put(self::KEY_MEMBER, $memberId);
    }

    public function setBillDiscount(float $amount): void
    {
        Session::put(self::KEY_DISCOUNT, max(0, $amount));
    }

    public function clear(): void
    {
        Session::forget([self::KEY_ITEMS, self::KEY_MEMBER, self::KEY_DISCOUNT]);
    }

    /* ---------------- คำนวณยอดเงิน ---------------- */

    /**
     * สรุปยอดของบิลปัจจุบัน
     *
     * subtotal        = ผลรวม (ราคา x จำนวน) - ส่วนลดรายการ
     * member_discount = ส่วนลดตามระดับสมาชิก (% ของ subtotal)
     * discount        = ส่วนลดท้ายบิลที่พนักงานกรอก
     * vat             = ภาษีมูลค่าเพิ่มแบบรวมในราคา (Included VAT)
     * total           = subtotal - member_discount - discount
     */
    public function summary(): array
    {
        $items    = $this->items();
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += ($item['price'] * $item['qty']) - $item['discount'];
        }

        $member         = $this->member();
        $memberRate     = $member?->discount_rate ?? 0;
        $memberDiscount = round($subtotal * $memberRate / 100, 2);

        $billDiscount = min($this->billDiscount(), max(0, $subtotal - $memberDiscount));

        $total   = max(0, round($subtotal - $memberDiscount - $billDiscount, 2));
        $vatRate = Setting::vatRate();
        $vat     = round($total - ($total * 100 / (100 + $vatRate)), 2);

        $pointRate    = Setting::pointRate();
        $pointsEarned = $member && $pointRate > 0 ? (int) floor($total / $pointRate) : 0;

        return [
            'item_count'      => count($items),
            'total_qty'       => $this->count(),
            'subtotal'        => round($subtotal, 2),
            'member_discount' => $memberDiscount,
            'member_rate'     => $memberRate,
            'discount'        => round($billDiscount, 2),
            'vat_rate'        => $vatRate,
            'vat'             => $vat,
            'total'           => $total,
            'points_earned'   => $pointsEarned,
        ];
    }
}
