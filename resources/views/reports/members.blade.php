@extends('layouts.app')
@section('title', 'รายงานสมาชิก')
@section('subtitle', 'ตั้งแต่ ' . \Carbon\Carbon::parse($from)->format('d/m/Y') . ' ถึง ' . \Carbon\Carbon::parse($to)->format('d/m/Y'))

@section('content')
@include('reports._filter')

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-people"></i></div>
            <div><div class="stat-label">สมาชิกทั้งหมด</div><div class="stat-value">{{ number_format($summary['members']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-person-plus"></i></div>
            <div><div class="stat-label">สมาชิกใหม่ในช่วงนี้</div><div class="stat-value">{{ number_format($summary['new_members']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-cash-stack"></i></div>
            <div><div class="stat-label">ยอดซื้อของสมาชิก</div><div class="stat-value text-money">{{ number_format($summary['period_total'], 0) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-star"></i></div>
            <div><div class="stat-label">แต้มสะสมคงค้าง</div><div class="stat-value">{{ number_format($summary['points']) }}</div></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-table me-2"></i>อันดับสมาชิกตามยอดซื้อในช่วงเวลา (50 อันดับแรก)</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th class="text-center">อันดับ</th><th>รหัส</th><th>ชื่อสมาชิก</th>
                    <th class="text-center">ระดับ</th><th class="text-center">จำนวนบิล</th>
                    <th class="text-end">ยอดซื้อในช่วงนี้</th><th class="text-end">ยอดซื้อสะสม</th><th class="text-center">แต้ม</th></tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $m)
                    <tr>
                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                        <td class="small fw-semibold">{{ $m->member_code }}</td>
                        <td><a href="{{ route('members.show', $m) }}" class="text-decoration-none">{{ $m->name }}</a></td>
                        <td class="text-center"><span class="badge bg-{{ $m->tier_badge }}">{{ $m->tier_name }}</span></td>
                        <td class="text-center">{{ number_format($m->bills) }}</td>
                        <td class="text-end text-money fw-semibold">{{ number_format($m->period_total ?? 0, 2) }}</td>
                        <td class="text-end text-money text-muted">{{ number_format($m->total_spent, 2) }}</td>
                        <td class="text-center">{{ number_format($m->points) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">ยังไม่มีข้อมูลสมาชิก</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
