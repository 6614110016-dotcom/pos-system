@extends('layouts.app')
@section('title', 'รายละเอียดสินค้า')
@section('subtitle', $product->product_code . ' - ' . $product->name)

@section('content')
<div class="mb-3 d-flex gap-2">
    <a href="{{ route('products.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>กลับ</a>
    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>แก้ไข</a>
    <a href="{{ route('stock.adjust.form', $product) }}" class="btn btn-warning"><i class="bi bi-sliders me-1"></i>ปรับยอดสต็อก</a>
    <a href="{{ route('stock.receive.form', ['product_id' => $product->id]) }}" class="btn btn-success"><i class="bi bi-box-arrow-in-down me-1"></i>รับสินค้าเข้า</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <img src="{{ $product->image_url }}" class="card-img-top" style="aspect-ratio:1;object-fit:cover;background:#f1f5f9"
                 onerror="this.src='{{ asset('images/no-image.svg') }}'" alt="">
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">รหัสสินค้า</span><span class="fw-semibold">{{ $product->product_code }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">บาร์โค้ด</span><span>{{ $product->barcode ?? '-' }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">หมวดหมู่</span><span>{{ $product->category->name ?? '-' }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">ราคาทุน</span><span class="text-money">{{ number_format($product->cost_price, 2) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">ราคาขาย</span><span class="text-money fw-semibold">{{ number_format($product->price, 2) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">กำไรต่อหน่วย</span><span class="text-money text-success">{{ number_format($product->profit_per_unit, 2) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">จุดสั่งซื้อ</span><span>{{ $product->reorder_level }} {{ $product->unit }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">สถานะ</span>
                    <span class="badge bg-{{ $product->status ? 'success' : 'secondary' }}">{{ $product->status ? 'เปิดขาย' : 'ปิดขาย' }}</span></li>
                @if($product->description)
                    <li class="list-group-item"><span class="text-muted">รายละเอียด:</span> {{ $product->description }}</li>
                @endif
            </ul>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-{{ $product->stock_badge }} bg-opacity-10 text-{{ $product->stock_badge }}"><i class="bi bi-boxes"></i></div>
                    <div><div class="stat-label">คงเหลือ</div><div class="stat-value">{{ number_format($product->stock_qty) }}</div>
                        <div class="small text-muted">{{ $product->unit }}</div></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-cart-check"></i></div>
                    <div><div class="stat-label">ขายไปแล้ว</div><div class="stat-value">{{ number_format($soldQty) }}</div></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-wallet2"></i></div>
                    <div><div class="stat-label">มูลค่าสต็อก (ทุน)</div>
                        <div class="stat-value text-money">{{ number_format($product->stock_qty * $product->cost_price, 0) }}</div></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-arrow-left-right me-2"></i>ประวัติการเคลื่อนไหวสต็อก</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>วันที่</th><th>ประเภท</th><th class="text-center">จำนวน</th>
                            <th class="text-center">ก่อน</th><th class="text-center">หลัง</th>
                            <th>อ้างอิง</th><th>ผู้ทำรายการ</th></tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $mv)
                            <tr>
                                <td class="small">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                                <td><span class="badge bg-{{ $mv->type_badge }}">{{ $mv->type_name }}</span></td>
                                <td class="text-center fw-semibold {{ $mv->qty > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $mv->qty > 0 ? '+' : '' }}{{ $mv->qty }}
                                </td>
                                <td class="text-center text-muted small">{{ $mv->qty_before }}</td>
                                <td class="text-center small">{{ $mv->qty_after }}</td>
                                <td class="small text-muted">{{ $mv->reference ?? '-' }}</td>
                                <td class="small text-muted">{{ $mv->user->name ?? 'ระบบ' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">ยังไม่มีการเคลื่อนไหวสต็อก</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($movements->hasPages())
                <div class="card-footer bg-white">{{ $movements->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
