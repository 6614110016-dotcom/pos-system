@extends('layouts.app')
@section('title', 'รายงานยอดขายตามพนักงาน')
@section('subtitle', 'ตั้งแต่ ' . \Carbon\Carbon::parse($from)->format('d/m/Y') . ' ถึง ' . \Carbon\Carbon::parse($to)->format('d/m/Y'))

@section('content')
@include('reports._filter')

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-people"></i></div>
            <div><div class="stat-label">พนักงานที่มียอดขาย</div><div class="stat-value">{{ number_format($summary['staff']) }}</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-receipt"></i></div>
            <div><div class="stat-label">จำนวนบิลรวม</div><div class="stat-value">{{ number_format($summary['bills']) }}</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-cash-stack"></i></div>
            <div><div class="stat-label">ยอดขายรวม</div><div class="stat-value text-money">{{ number_format($summary['total'], 2) }}</div></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><i class="bi bi-bar-chart me-2"></i>เปรียบเทียบยอดขาย</div>
    <div class="card-body"><canvas id="empChart" height="90"></canvas></div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-table me-2"></i>ตารางยอดขายแยกตามพนักงาน</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th class="text-center">อันดับ</th><th>พนักงาน</th><th class="text-center">สิทธิ์</th>
                    <th class="text-center">จำนวนบิล</th><th class="text-end">ยอดขายรวม</th>
                    <th class="text-end">เฉลี่ย/บิล</th><th class="text-end">สัดส่วน</th></tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $row)
                    <tr>
                        <td class="text-center"><span class="badge bg-{{ $i < 3 ? ['warning','secondary','danger'][$i] : 'light text-dark' }}">{{ $i + 1 }}</span></td>
                        <td>
                            <div class="fw-semibold small">{{ $row->user->name ?? '-' }}</div>
                            <div class="text-muted" style="font-size:.72rem">{{ $row->user->employee_code ?? '' }}</div>
                        </td>
                        <td class="text-center"><span class="badge bg-{{ $row->user->role_badge ?? 'secondary' }}">{{ $row->user->role_name ?? '-' }}</span></td>
                        <td class="text-center">{{ number_format($row->bills) }}</td>
                        <td class="text-end text-money fw-semibold">{{ number_format($row->total, 2) }}</td>
                        <td class="text-end text-money">{{ number_format($row->avg_bill, 2) }}</td>
                        <td class="text-end">{{ $summary['total'] > 0 ? number_format($row->total / $summary['total'] * 100, 1) : 0 }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">ไม่มีข้อมูลการขายในช่วงเวลาที่เลือก</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const rows = {{ \Illuminate\Support\Js::from($rows->map(fn ($r) => ['name' => $r->user->name ?? '-', 'total' => (float) $r->total])) }};

    if (rows.length) {
        new Chart(document.getElementById('empChart'), {
            type: 'bar',
            data: {
                labels: rows.map(r => r.name),
                datasets: [{ label: 'ยอดขาย (บาท)', data: rows.map(r => r.total), backgroundColor: 'rgba(22,163,74,.75)', borderRadius: 6 }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('th-TH') } } }
            }
        });
    }
</script>
@endpush
