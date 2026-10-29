@extends('layouts.app')
@section('title', 'ประวัติการขาย')
@section('subtitle', 'ค้นหาและตรวจสอบบิลขายทั้งหมดของร้าน')

@section('content')

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-receipt"></i></div>
            <div><div class="stat-label">จำนวนบิลที่ค้นพบ</div><div class="stat-value">{{ number_format($summary['count']) }}</div></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-cash-stack"></i></div>
            <div><div class="stat-label">ยอดขายรวม (เฉพาะบิลที่ชำระแล้ว)</div><div class="stat-value text-money">{{ number_format($summary['total'], 2) }}</div></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="ค้นหาเลขที่บิล">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">ทุกสถานะ</option>
                    <option value="paid" @selected(request('status') === 'paid')>ชำระแล้ว</option>
                    <option value="pending" @selected(request('status') === 'pending')>รอชำระเงิน</option>
                    <option value="void" @selected(request('status') === 'void')>ยกเลิก</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>ค้นหา</button>
                <a href="{{ route('sales.index') }}" class="btn btn-light">ล้าง</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>เลขที่บิล</th>
                    <th>วันที่-เวลา</th>
                    <th>ลูกค้า</th>
                    <th>พนักงานขาย</th>
                    <th class="text-center">รายการ</th>
                    <th class="text-end">ยอดสุทธิ</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr class="{{ $sale->status === 'void' ? 'text-decoration-line-through opacity-75' : '' }}">
                        <td class="fw-semibold">{{ $sale->sale_no }}</td>
                        <td class="small">{{ ($sale->sold_at ?? $sale->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->customer_name }}</td>
                        <td class="small text-muted">{{ $sale->user->name ?? '-' }}</td>
                        <td class="text-center">{{ $sale->items()->count() }}</td>
                        <td class="text-end text-money fw-semibold">{{ number_format($sale->total, 2) }}</td>
                        <td class="text-center"><span class="badge bg-{{ $sale->status_badge }}">{{ $sale->status_name }}</span></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-primary" title="รายละเอียด"><i class="bi bi-eye"></i></a>
                                @if($sale->status === 'paid')
                                    <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-outline-secondary" title="ใบเสร็จ"><i class="bi bi-printer"></i></a>
                                @elseif($sale->status === 'pending')
                                    <a href="{{ route('payments.create', $sale) }}" class="btn btn-outline-success" title="รับชำระเงิน"><i class="bi bi-cash-coin"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">ไม่พบรายการขายตามเงื่อนไขที่ค้นหา</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sales->hasPages())
        <div class="card-footer bg-white">{{ $sales->links() }}</div>
    @endif
</div>
@endsection
