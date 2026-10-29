<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * โมดูลสินค้า (Stock) - จัดการข้อมูลสินค้า
 */
class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->search($request->q)
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->stock === 'low', fn ($q) => $q->lowStock())
            ->when($request->stock === 'out', fn ($q) => $q->where('stock_qty', '<=', 0))
            ->when($request->filled('status'), fn ($q) => $q->where('status', (bool) $request->status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::active()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        return view('products.create', [
            'categories' => Category::active()->orderBy('name')->get(),
            'nextCode'   => Product::generateCode(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['product_code'] = Product::generateCode();
        $data['status']       = $request->boolean('status', true);
        $data['image']        = $this->uploadImage($request);

        $openingStock      = (int) $request->input('stock_qty', 0);
        $data['stock_qty'] = 0;

        $product = Product::create($data);

        // บันทึกสต็อกยกมาเป็นการรับเข้าครั้งแรก
        if ($openingStock > 0) {
            $product->adjustStock($openingStock, 'in', 'OPENING', 'สต็อกยกมาตอนสร้างสินค้า');
        }

        ActivityLog::record('create', 'Product', "เพิ่มสินค้า {$product->product_code} - {$product->name}");

        return redirect()->route('products.index')
            ->with('success', "เพิ่มสินค้า \"{$product->name}\" เรียบร้อยแล้ว (รหัส {$product->product_code})");
    }

    public function show(Product $product): View
    {
        $product->load('category');

        $movements = $product->stockMovements()
            ->with('user')
            ->latest('id')
            ->paginate(15);

        $soldQty = (int) $product->saleItems()
            ->whereHas('sale', fn ($q) => $q->paid())
            ->sum('qty');

        return view('products.show', compact('product', 'movements', 'soldQty'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product'    => $product,
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateData($request, $product->id);
        $data['status'] = $request->boolean('status');

        // จำนวนคงเหลือแก้ไขผ่านเมนู "จัดการสต็อก" เท่านั้น
        unset($data['stock_qty']);

        if ($image = $this->uploadImage($request)) {
            $this->deleteImage($product->image);
            $data['image'] = $image;
        }

        $product->update($data);
        ActivityLog::record('update', 'Product', "แก้ไขสินค้า {$product->product_code}");

        return redirect()->route('products.index')
            ->with('success', "แก้ไขข้อมูลสินค้า \"{$product->name}\" เรียบร้อยแล้ว");
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->saleItems()->exists()) {
            return back()->with('error', 'ไม่สามารถลบสินค้าที่เคยมีการขายแล้วได้ กรุณาปิดสถานะการขายแทน');
        }

        $name = $product->name;
        $this->deleteImage($product->image);
        $product->stockMovements()->delete();
        $product->delete();

        ActivityLog::record('delete', 'Product', "ลบสินค้า {$name}");

        return redirect()->route('products.index')->with('success', "ลบสินค้า \"{$name}\" เรียบร้อยแล้ว");
    }

    /** ค้นหาสินค้าจากหน้าจอ POS (AJAX) */
    public function search(Request $request)
    {
        $products = Product::active()
            ->search($request->q)
            ->where('stock_qty', '>', 0)
            ->limit(12)
            ->get();

        return response()->json(
            $products->map(fn ($p) => [
                'id'    => $p->id,
                'code'  => $p->product_code,
                'name'  => $p->name,
                'price' => (float) $p->price,
                'stock' => $p->stock_qty,
                'unit'  => $p->unit,
            ])
        );
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:200'],
            'barcode'       => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($ignoreId)],
            'category_id'   => ['required', 'exists:categories,id'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'unit'          => ['required', 'string', 'max:20'],
            'cost_price'    => ['required', 'numeric', 'min:0'],
            'price'         => ['required', 'numeric', 'min:0', 'gte:cost_price'],
            'stock_qty'     => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required'          => 'กรุณากรอกชื่อสินค้า',
            'barcode.unique'         => 'บาร์โค้ดนี้ถูกใช้กับสินค้าอื่นแล้ว',
            'category_id.required'   => 'กรุณาเลือกหมวดหมู่สินค้า',
            'unit.required'          => 'กรุณาระบุหน่วยนับ',
            'cost_price.required'    => 'กรุณากรอกราคาทุน',
            'price.required'         => 'กรุณากรอกราคาขาย',
            'price.gte'              => 'ราคาขายต้องไม่ต่ำกว่าราคาทุน',
            'reorder_level.required' => 'กรุณากำหนดจุดสั่งซื้อ',
            'image.image'            => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพ',
            'image.max'              => 'ขนาดรูปภาพต้องไม่เกิน 2 MB',
        ]);
    }

    protected function uploadImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $name = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads/products'), $name);

        return $name;
    }

    protected function deleteImage(?string $image): void
    {
        if ($image && file_exists(public_path('uploads/products/'.$image))) {
            @unlink(public_path('uploads/products/'.$image));
        }
    }
}
