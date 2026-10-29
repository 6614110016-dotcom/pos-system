<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * โมดูลสินค้า (Stock) - รับสินค้าเข้า / ปรับยอด / ประวัติการเคลื่อนไหว
 */
class StockController extends Controller
{
    /** ภาพรวมสต็อกทั้งหมด */
    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->search($request->q)
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->filter === 'low', fn ($q) => $q->lowStock())
            ->when($request->filter === 'out', fn ($q) => $q->where('stock_qty', '<=', 0))
            ->orderBy('stock_qty')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::active()->orderBy('name')->get();

        $summary = [
            'total_items' => Product::active()->count(),
            'total_qty'   => (int) Product::active()->sum('stock_qty'),
            'stock_value' => (float) Product::active()->sum(DB::raw('stock_qty * cost_price')),
            'low_stock'   => Product::active()->lowStock()->count(),
        ];

        return view('stock.index', compact('products', 'categories', 'summary'));
    }

    /** ฟอร์มรับสินค้าเข้าคลัง */
    public function receiveForm(Request $request): View
    {
        return view('stock.receive', [
            'products'  => Product::active()->orderBy('name')->get(),
            'productId' => $request->product_id,
        ]);
    }

    /** บันทึกการรับสินค้าเข้า (รองรับหลายรายการในครั้งเดียว) */
    public function receive(Request $request): RedirectResponse
    {
        $request->validate([
            'reference'            => ['nullable', 'string', 'max:50'],
            'note'                 => ['nullable', 'string', 'max:255'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'exists:products,id'],
            'items.*.qty'          => ['required', 'integer', 'min:1'],
            'items.*.cost_price'   => ['nullable', 'numeric', 'min:0'],
        ], [
            'items.required'          => 'กรุณาเพิ่มรายการสินค้าอย่างน้อย 1 รายการ',
            'items.*.qty.required'    => 'กรุณาระบุจำนวนที่รับเข้า',
            'items.*.qty.min'         => 'จำนวนที่รับเข้าต้องมากกว่า 0',
        ]);

        $reference = $request->reference ?: 'RC-'.now()->format('YmdHis');

        DB::transaction(function () use ($request, $reference) {
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (! empty($item['cost_price'])) {
                    $product->update(['cost_price' => $item['cost_price']]);
                }

                $product->adjustStock(
                    (int) $item['qty'],
                    'in',
                    $reference,
                    $request->note
                );
            }
        });

        ActivityLog::record('create', 'Stock', "รับสินค้าเข้าคลัง เลขที่ {$reference}");

        return redirect()->route('stock.movements')
            ->with('success', "บันทึกการรับสินค้าเข้าคลังเรียบร้อยแล้ว (เลขที่ {$reference})");
    }

    /** ฟอร์มปรับยอดสต็อก (กรณีของเสีย / นับสต็อกไม่ตรง) */
    public function adjustForm(Product $product): View
    {
        return view('stock.adjust', compact('product'));
    }

    /** บันทึกการปรับยอดสต็อก */
    public function adjust(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'actual_qty' => ['required', 'integer', 'min:0'],
            'note'       => ['required', 'string', 'max:255'],
        ], [
            'actual_qty.required' => 'กรุณาระบุจำนวนที่นับได้จริง',
            'note.required'       => 'กรุณาระบุเหตุผลในการปรับยอด',
        ]);

        $diff = (int) $request->actual_qty - $product->stock_qty;

        if ($diff === 0) {
            return back()->with('error', 'จำนวนที่นับได้เท่ากับยอดในระบบ ไม่ต้องปรับยอด');
        }

        $product->adjustStock($diff, 'adjust', 'ADJ-'.now()->format('YmdHis'), $request->note);

        ActivityLog::record('update', 'Stock', "ปรับยอดสต็อก {$product->name} จำนวน {$diff}");

        return redirect()->route('stock.index')
            ->with('success', "ปรับยอดสต็อก \"{$product->name}\" เรียบร้อยแล้ว ({$diff} {$product->unit})");
    }

    /** ประวัติการเคลื่อนไหวสต็อกทั้งหมด */
    public function movements(Request $request): View
    {
        $movements = StockMovement::with(['product', 'user'])
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->product_id, fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->from && $request->to, fn ($q) => $q->whereBetween('created_at', [
                $request->from.' 00:00:00',
                $request->to.' 23:59:59',
            ]))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name', 'product_code']);

        return view('stock.movements', compact('movements', 'products'));
    }
}
