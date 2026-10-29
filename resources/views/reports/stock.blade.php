@extends('layouts.app')
@section('title', 'รายงานสต็อกคงเหลือ')
@section('subtitle', 'ข้อมูล ณ วันที่ ' . now()->format('d/m/Y H:i') . ' น.')

@section('content')

<div class="card mb-3 no-print">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
            <select name="filter" class="form-select" style="max-width:220px">
                <option value="">สินค้าทั้งหมด</option>
                <option value="low" @selected(request('filter') === 'low')>เฉพาะสินค้าใกล้หมด</option>
                <option value="out" @selected(request('filter') === 'out')>เฉพาะสินค้าหมดสต็อก</option>
            </select>
            <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>แสดงรายงาน</button>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>พิมพ์</button>
            <a href="{{ route('reports.index') }}" class="btn btn-light ms-auto">เมนูรายงาน</a>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-box-seam"></i></div>
            <div><div class="stat-label">รายการสินค้า</div><div class="stat-value">{{ number_format($summary['items']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-boxes"></i></div>
            <div><div class="stat-label">จำนวนคงเหลือรวม</div><div class="stat-value">{{ number_format($summary['qty']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-wallet2"></i></div>
            <div><div class="stat-label">มูลค่าตามราคาทุน</div><div class="stat-value text-money">{{ number_format($summary['cost_value'], 0) }}</div>
                <div class="small text-muted">ราคาขาย {{ number_format($summary['sale_value'], 0) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-exclamation-triangle"></i></div>
            <div><div class="stat-label">ต้องสั่งเพิ่ม</div><div class="stat-value">{{ number_format($summary['low']) }}</div></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-table me-2"></i>รายละเอียดสต็อกสินค้า</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>รหัส</th><th>ชื่อสินค้า</th><th>หมวดหมู่</th>
                    <th class="text-center">คงเหลือ</th><th class="text-center">จุดสั่งซื้อ</th>
                    <th class="text-end">ราคาทุน</th><th class="text-end">มูลค่าทุน</th>
                    <th class="text-end">มูลค่าขาย</th><th class="text-center">สถานะ</th></tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td class="small fw-semibold">{{ $p->product_code }}</td>
                        <td class="small">{{ $p->name }}</td>
                        <td class="small text-muted">{{ $p->category->name ?? '-' }}</td>
                        <td class="text-center fw-semibold">{{ number_format($p->stock_qty) }}</td>
                        <td class="text-center text-muted">{{ $p->reorder_level }}</td>
                        <td class="text-end text-money">{{ number_format($p->cost_price, 2) }}</td>
                        <td class="text-end text-money">{{ number_format($p->stock_qty * $p->cost_price, 2) }}</td>
                        <td class="text-end text-money text-muted">{{ number_format($p->stock_qty * $p->price, 2) }}</td>
                        <td class="text-center">
                            @if($p->stock_qty <= 0)
                                <span class="badge bg-danger">หมดสต็อก</span>
                            @elseif($p->is_low_stock)
                                <span class="badge bg-warning text-dark">ใกล้หมด</span>
                            @else
                                <span class="badge bg-success">ปกติ</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-5">ไม่พบสินค้าตามเงื่อนไข</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="3">รวมทั้งสิ้น</td>
                    <td class="text-center">{{ number_format($summary['qty']) }}</td>
                    <td colspan="2"></td>
                    <td class="text-end text-money">{{ number_format($summary['cost_value'], 2) }}</td>
                    <td class="text-end text-money">{{ number_format($summary['sale_value'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
