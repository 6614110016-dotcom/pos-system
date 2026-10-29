@extends('layouts.app')
@section('title', 'โมดูลสินค้า')
@section('subtitle', 'จัดการข้อมูลสินค้า ราคาขาย และจุดสั่งซื้อ')

@section('content')
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="ค้นหาชื่อ / รหัส / บาร์โค้ด">
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select">
                    <option value="">ทุกหมวดหมู่</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="stock" class="form-select">
                    <option value="">สต็อกทั้งหมด</option>
                    <option value="low" @selected(request('stock') === 'low')>ใกล้หมด</option>
                    <option value="out" @selected(request('stock') === 'out')>หมดสต็อก</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">ทุกสถานะ</option>
                    <option value="1" @selected(request('status') === '1')>เปิดขาย</option>
                    <option value="0" @selected(request('status') === '0')>ปิดขาย</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('products.index') }}" class="btn btn-light">ล้าง</a>
                <a href="{{ route('products.create') }}" class="btn btn-success ms-auto text-nowrap">
                    <i class="bi bi-plus-lg me-1"></i>เพิ่มสินค้า
                </a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:60px"></th>
                    <th>รหัส / ชื่อสินค้า</th><th>หมวดหมู่</th>
                    <th class="text-end">ทุน</th><th class="text-end">ราคาขาย</th>
                    <th class="text-center">คงเหลือ</th><th class="text-center">สถานะ</th>
                    <th class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td>
                            <img src="{{ $p->image_url }}" style="width:44px;height:44px;object-fit:cover;border-radius:.35rem;background:#f1f5f9"
                                 onerror="this.src='{{ asset('images/no-image.svg') }}'" alt="">
                        </td>
                        <td>
                            <a href="{{ route('products.show', $p) }}" class="text-decoration-none fw-semibold">{{ $p->name }}</a>
                            <div class="text-muted" style="font-size:.75rem">
                                {{ $p->product_code }} @if($p->barcode) &middot; {{ $p->barcode }} @endif
                            </div>
                        </td>
                        <td class="small">{{ $p->category->name ?? '-' }}</td>
                        <td class="text-end text-money small text-muted">{{ number_format($p->cost_price, 2) }}</td>
                        <td class="text-end text-money fw-semibold">{{ number_format($p->price, 2) }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $p->stock_badge }}">{{ $p->stock_qty }}</span>
                            <div class="text-muted" style="font-size:.72rem">{{ $p->unit }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $p->status ? 'success' : 'secondary' }}">{{ $p->status ? 'เปิดขาย' : 'ปิดขาย' }}</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('products.show', $p) }}" class="btn btn-outline-primary" title="รายละเอียด"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('products.edit', $p) }}" class="btn btn-outline-secondary" title="แก้ไข"><i class="bi bi-pencil"></i></a>
                                <a href="{{ route('stock.adjust.form', $p) }}" class="btn btn-outline-warning" title="ปรับสต็อก"><i class="bi bi-sliders"></i></a>
                                <form method="POST" action="{{ route('products.destroy', $p) }}" data-confirm="ยืนยันการลบสินค้า {{ $p->name }} ?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">ไม่พบสินค้าตามเงื่อนไขที่ค้นหา</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="card-footer bg-white">{{ $products->links() }}</div>
    @endif
</div>
@endsection
