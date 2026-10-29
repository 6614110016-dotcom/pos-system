@extends('layouts.app')
@section('title', 'จัดการสต็อกสินค้า')
@section('subtitle', 'ตรวจสอบยอดคงเหลือ มูลค่าสต็อก และสินค้าที่ต้องสั่งเพิ่ม')

@section('content')

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-box-seam"></i></div>
            <div><div class="stat-label">รายการสินค้า</div><div class="stat-value">{{ number_format($summary['total_items']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-boxes"></i></div>
            <div><div class="stat-label">จำนวนคงเหลือรวม</div><div class="stat-value">{{ number_format($summary['total_qty']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-wallet2"></i></div>
            <div><div class="stat-label">มูลค่าสต็อก (ราคาทุน)</div><div class="stat-value text-money">{{ number_format($summary['stock_value'], 0) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-exclamation-triangle"></i></div>
            <div><div class="stat-label">ต้องสั่งเพิ่ม</div><div class="stat-value">{{ number_format($summary['low_stock']) }}</div></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="ค้นหาสินค้า">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">ทุกหมวดหมู่</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="filter" class="form-select">
                    <option value="">ทั้งหมด</option>
                    <option value="low" @selected(request('filter') === 'low')>ใกล้หมด</option>
                    <option value="out" @selected(request('filter') === 'out')>หมดสต็อก</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('stock.index') }}" class="btn btn-light">ล้าง</a>
                <a href="{{ route('stock.receive.form') }}" class="btn btn-success ms-auto text-nowrap">
                    <i class="bi bi-box-arrow-in-down me-1"></i>รับสินค้าเข้า
                </a>
                <a href="{{ route('stock.movements') }}" class="btn btn-outline-secondary text-nowrap">
                    <i class="bi bi-clock-history me-1"></i>ประวัติ
                </a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>รหัส</th><th>ชื่อสินค้า</th><th>หมวดหมู่</th>
                    <th class="text-center">คงเหลือ</th><th class="text-center">จุดสั่งซื้อ</th>
                    <th class="text-end">มูลค่าทุน</th><th class="text-center">สถานะสต็อก</th><th class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td class="small fw-semibold">{{ $p->product_code }}</td>
                        <td>{{ $p->name }}</td>
                        <td class="small text-muted">{{ $p->category->name ?? '-' }}</td>
                        <td class="text-center fw-semibold">{{ number_format($p->stock_qty) }} <span class="text-muted small">{{ $p->unit }}</span></td>
                        <td class="text-center text-muted">{{ $p->reorder_level }}</td>
                        <td class="text-end text-money">{{ number_format($p->stock_qty * $p->cost_price, 2) }}</td>
                        <td class="text-center">
                            @if($p->stock_qty <= 0)
                                <span class="badge bg-danger">หมดสต็อก</span>
                            @elseif($p->is_low_stock)
                                <span class="badge bg-warning text-dark">ใกล้หมด</span>
                            @else
                                <span class="badge bg-success">ปกติ</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('stock.receive.form', ['product_id' => $p->id]) }}" class="btn btn-outline-success" title="รับเข้า">
                                    <i class="bi bi-box-arrow-in-down"></i>
                                </a>
                                <a href="{{ route('stock.adjust.form', $p) }}" class="btn btn-outline-warning" title="ปรับยอด">
                                    <i class="bi bi-sliders"></i>
                                </a>
                                <a href="{{ route('products.show', $p) }}" class="btn btn-outline-primary" title="ประวัติ">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">ไม่พบสินค้าตามเงื่อนไข</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="card-footer bg-white">{{ $products->links() }}</div>
    @endif
</div>
@endsection
