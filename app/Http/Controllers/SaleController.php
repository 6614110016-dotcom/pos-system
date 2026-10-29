<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Services\PosCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * โมดูลการขาย (Sale)
 * - หน้าจอขายสินค้า (POS) : เลือกสินค้า -> ตะกร้า -> ผูกสมาชิก -> ส่วนลด -> เปิดบิล
 * - ประวัติการขาย / ดูรายละเอียดบิล / ยกเลิกบิล (คืนสต็อก)
 */
class SaleController extends Controller
{
    public function __construct(protected PosCart $cart)
    {
    }

    /* ==================== หน้าจอขายสินค้า ==================== */

    public function pos(Request $request): View
    {
        $products = Product::with('category')
            ->active()
            ->search($request->q)
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('sales.pos', [
            'products'   => $products,
            'categories' => Category::active()->orderBy('name')->get(),
            'items'      => $this->cart->items(),
            'summary'    => $this->cart->summary(),
            'member'     => $this->cart->member(),
        ]);
    }

    /** เพิ่มสินค้าลงตะกร้า (รองรับทั้งการคลิกและการยิงบาร์โค้ด) */
    public function addToCart(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'barcode'    => ['nullable', 'string'],
            'qty'        => ['nullable', 'integer', 'min:1'],
        ]);

        $product = $request->product_id
            ? Product::find($request->product_id)
            : Product::active()
                ->where(fn ($q) => $q->where('barcode', $request->barcode)
                                     ->orWhere('product_code', $request->barcode))
                ->first();

        if (! $product) {
            return back()->with('error', 'ไม่พบสินค้าที่ค้นหา กรุณาตรวจสอบรหัสหรือบาร์โค้ดอีกครั้ง');
        }

        if (! $product->status) {
            return back()->with('error', "สินค้า \"{$product->name}\" ถูกปิดการขายอยู่");
        }

        $result = $this->cart->add($product, (int) $request->input('qty', 1));

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /** ปรับจำนวนสินค้าในตะกร้า */
    public function updateCart(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer'],
            'qty'        => ['required', 'integer', 'min:0'],
        ]);

        $result = $this->cart->updateQty((int) $request->product_id, (int) $request->qty);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /** กำหนดส่วนลดรายการสินค้า */
    public function itemDiscount(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer'],
            'discount'   => ['required', 'numeric', 'min:0'],
        ]);

        $result = $this->cart->updateItemDiscount((int) $request->product_id, (float) $request->discount);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /** นำสินค้าออกจากตะกร้า */
    public function removeFromCart(Request $request): RedirectResponse
    {
        $result = $this->cart->remove((int) $request->product_id);

        return back()->with('success', $result['message']);
    }

    /** ล้างตะกร้าทั้งหมด */
    public function clearCart(): RedirectResponse
    {
        $this->cart->clear();

        return back()->with('success', 'ล้างตะกร้าสินค้าเรียบร้อยแล้ว');
    }

    /** ผูก/ยกเลิกสมาชิกกับบิลปัจจุบัน */
    public function setMember(Request $request): RedirectResponse
    {
        $request->validate(['member_id' => ['nullable', 'exists:members,id']]);

        $this->cart->setMember($request->member_id ? (int) $request->member_id : null);

        return back()->with('success', $request->member_id
            ? 'ผูกข้อมูลสมาชิกกับบิลนี้แล้ว'
            : 'ยกเลิกการผูกสมาชิกแล้ว');
    }

    /** กำหนดส่วนลดท้ายบิล */
    public function setDiscount(Request $request): RedirectResponse
    {
        $request->validate([
            'discount' => ['required', 'numeric', 'min:0'],
        ], [
            'discount.required' => 'กรุณากรอกจำนวนส่วนลด',
        ]);

        $this->cart->setBillDiscount((float) $request->discount);

        return back()->with('success', 'บันทึกส่วนลดท้ายบิลแล้ว');
    }

    /* ==================== เปิดบิล ==================== */

    /**
     * สร้างบิลขายจากตะกร้า (สถานะ pending) แล้วตัดสต็อกทันที
     * จากนั้นส่งต่อไปหน้าชำระเงิน (โมดูล Payment)
     */
    public function checkout(Request $request): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return back()->with('error', 'ตะกร้าสินค้าว่าง กรุณาเลือกสินค้าก่อนเปิดบิล');
        }

        $items   = $this->cart->items();
        $summary = $this->cart->summary();
        $member  = $this->cart->member();

        // ตรวจสอบสต็อกอีกครั้งก่อนเปิดบิล (กันการขายเกินสต็อก)
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (! $product || $product->stock_qty < $item['qty']) {
                $remain = $product?->stock_qty ?? 0;

                return back()->with('error', "สินค้า \"{$item['name']}\" มีคงเหลือเพียง {$remain} หน่วย ไม่พอขาย");
            }
        }

        $sale = DB::transaction(function () use ($items, $summary, $member, $request) {
            $sale = Sale::create([
                'sale_no'         => Sale::generateSaleNo(),
                'member_id'       => $member?->id,
                'user_id'         => auth()->id(),
                'subtotal'        => $summary['subtotal'],
                'discount'        => $summary['discount'],
                'member_discount' => $summary['member_discount'],
                'vat'             => $summary['vat'],
                'total'           => $summary['total'],
                'points_earned'   => $summary['points_earned'],
                'status'          => 'pending',
                'note'            => $request->input('note'),
            ]);

            foreach ($items as $item) {
                $sale->items()->create([
                    'product_id'   => $item['product_id'],
                    'product_code' => $item['product_code'],
                    'product_name' => $item['name'],
                    'price'        => $item['price'],
                    'cost_price'   => $item['cost_price'],
                    'qty'          => $item['qty'],
                    'discount'     => $item['discount'],
                    'subtotal'     => ($item['price'] * $item['qty']) - $item['discount'],
                ]);

                // ตัดสต็อกออกจากคลัง
                Product::find($item['product_id'])
                    ->adjustStock(-$item['qty'], 'out', $sale->sale_no, 'ตัดสต็อกจากการขาย');
            }

            return $sale;
        });

        $this->cart->clear();
        ActivityLog::record('sale', 'Sale', "เปิดบิลขาย {$sale->sale_no} ยอด {$sale->total} บาท");

        return redirect()->route('payments.create', $sale)
            ->with('success', "เปิดบิล {$sale->sale_no} เรียบร้อยแล้ว กรุณารับชำระเงิน");
    }

    /* ==================== ประวัติการขาย ==================== */

    public function index(Request $request): View
    {
        $sales = Sale::with(['member', 'user'])
            ->when($request->q, fn ($q, $term) => $q->where('sale_no', 'like', "%{$term}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->from && $request->to, fn ($q) => $q->whereBetween('created_at', [
                $request->from.' 00:00:00',
                $request->to.' 23:59:59',
            ]))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'count' => (clone $sales)->total(),
            'total' => (float) Sale::paid()
                ->when($request->from && $request->to, fn ($q) => $q->whereBetween('created_at', [
                    $request->from.' 00:00:00',
                    $request->to.' 23:59:59',
                ]))->sum('total'),
        ];

        return view('sales.index', compact('sales', 'summary'));
    }

    public function show(Sale $sale): View
    {
        $sale->load(['items', 'payments.user', 'member', 'user', 'voidedBy']);

        return view('sales.show', compact('sale'));
    }

    /** ใบเสร็จรับเงิน (สำหรับพิมพ์) */
    public function receipt(Sale $sale): View
    {
        $sale->load(['items', 'payments', 'member', 'user']);

        return view('sales.receipt', compact('sale'));
    }

    /** ยกเลิกบิล - คืนสต็อก คืนแต้ม เฉพาะ admin/manager */
    public function void(Request $request, Sale $sale): RedirectResponse
    {
        $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ], [
            'void_reason.required' => 'กรุณาระบุเหตุผลในการยกเลิกบิล',
        ]);

        if ($sale->status === 'void') {
            return back()->with('error', 'บิลนี้ถูกยกเลิกไปแล้ว');
        }

        DB::transaction(function () use ($sale, $request) {
            // คืนสต็อกสินค้าทุกรายการ
            foreach ($sale->items as $item) {
                Product::find($item->product_id)
                    ?->adjustStock($item->qty, 'return', $sale->sale_no, 'คืนสต็อกจากการยกเลิกบิล');
            }

            // คืนแต้มและยอดซื้อสะสมของสมาชิก
            if ($sale->member && $sale->status === 'paid') {
                $sale->member->decrement('points', $sale->points_earned);
                $sale->member->decrement('total_spent', $sale->total);
                $sale->member->refresh()->refreshTier();
            }

            $sale->payments()->update(['status' => 'refunded']);

            $sale->update([
                'status'      => 'void',
                'voided_by'   => auth()->id(),
                'voided_at'   => now(),
                'void_reason' => $request->void_reason,
            ]);
        });

        ActivityLog::record('void', 'Sale', "ยกเลิกบิล {$sale->sale_no} เหตุผล: {$request->void_reason}");

        return redirect()->route('sales.show', $sale)
            ->with('success', "ยกเลิกบิล {$sale->sale_no} และคืนสต็อกเรียบร้อยแล้ว");
    }

    /** ค้นหาสินค้าแบบ AJAX สำหรับหน้าจอ POS */
    public function quickSearch(Request $request): JsonResponse
    {
        $products = Product::active()
            ->search($request->q)
            ->limit(10)
            ->get();

        return response()->json(
            $products->map(fn ($p) => [
                'id'    => $p->id,
                'code'  => $p->product_code,
                'name'  => $p->name,
                'price' => number_format((float) $p->price, 2),
                'stock' => $p->stock_qty,
                'unit'  => $p->unit,
            ])
        );
    }
}
