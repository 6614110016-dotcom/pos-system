@extends('layouts.app')
@section('title', 'ข้อมูลสมาชิก')
@section('subtitle', $member->member_code . ' - ' . $member->name)

@section('content')
<div class="mb-3 d-flex gap-2">
    <a href="{{ route('members.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>กลับ</a>
    <a href="{{ route('members.edit', $member) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>แก้ไขข้อมูล</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar mx-auto mb-2" style="width:64px;height:64px;font-size:1.6rem">{{ mb_substr($member->name, 0, 1) }}</div>
                <h5 class="mb-1">{{ $member->name }}</h5>
                <span class="badge bg-{{ $member->tier_badge }}">{{ $member->tier_name }}</span>
                <span class="badge bg-{{ $member->status ? 'success' : 'secondary' }}">{{ $member->status ? 'ใช้งาน' : 'ระงับ' }}</span>
            </div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">รหัสสมาชิก</span><span class="fw-semibold">{{ $member->member_code }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">เบอร์โทร</span><span>{{ $member->phone }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">อีเมล</span><span>{{ $member->email ?? '-' }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">เพศ</span><span>{{ $member->gender_name }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">วันเกิด</span><span>{{ $member->birth_date?->format('d/m/Y') ?? '-' }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">สมัครเมื่อ</span><span>{{ $member->joined_at?->format('d/m/Y') ?? '-' }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">ส่วนลดที่ได้รับ</span><span class="fw-semibold text-success">{{ $member->discount_rate }}%</span></li>
                @if($member->address)
                    <li class="list-group-item"><span class="text-muted">ที่อยู่:</span> {{ $member->address }}</li>
                @endif
            </ul>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-receipt"></i></div>
                    <div><div class="stat-label">จำนวนบิล</div><div class="stat-value">{{ number_format($summary['bills']) }}</div></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-cash-stack"></i></div>
                    <div><div class="stat-label">ยอดซื้อสะสม</div><div class="stat-value text-money">{{ number_format($summary['total'], 0) }}</div></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-star"></i></div>
                    <div><div class="stat-label">แต้มสะสม</div><div class="stat-value">{{ number_format($member->points) }}</div></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history me-2"></i>ประวัติการซื้อล่าสุด</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>เลขที่บิล</th><th>วันที่</th><th class="text-end">ยอดสุทธิ</th><th class="text-center">แต้ม</th><th class="text-center">สถานะ</th></tr></thead>
                    <tbody>
                        @forelse($member->sales as $sale)
                            <tr>
                                <td><a href="{{ route('sales.show', $sale) }}" class="text-decoration-none fw-semibold">{{ $sale->sale_no }}</a></td>
                                <td class="small">{{ ($sale->sold_at ?? $sale->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-end text-money">{{ number_format($sale->total, 2) }}</td>
                                <td class="text-center">+{{ $sale->points_earned }}</td>
                                <td class="text-center"><span class="badge bg-{{ $sale->status_badge }}">{{ $sale->status_name }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีประวัติการซื้อ</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
