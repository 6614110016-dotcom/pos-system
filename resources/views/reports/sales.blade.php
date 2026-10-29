@extends('layouts.app')
@section('title', 'รายงานยอดขายรายวัน')
@section('subtitle', 'ตั้งแต่ ' . \Carbon\Carbon::parse($from)->format('d/m/Y') . ' ถึง ' . \Carbon\Carbon::parse($to)->format('d/m/Y'))

@section('content')
@include('reports._filter', ['exportUrl' => route('reports.sales.export', ['from' => $from, 'to' => $to])])

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-receipt"></i></div>
            <div><div class="stat-label">จำนวนบิล</div><div class="stat-value">{{ number_format($summary['bills']) }}</div>
                <div class="small text-muted">ยกเลิก {{ $voided }} บิล</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-cash-stack"></i></div>
            <div><div class="stat-label">ยอดขายสุทธิ</div><div class="stat-value text-money">{{ number_format($summary['total'], 2) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-graph-up-arrow"></i></div>
            <div><div class="stat-label">กำไรขั้นต้น</div><div class="stat-value text-money">{{ number_format($summary['profit'], 2) }}</div>
                <div class="small text-muted">{{ $summary['total'] > 0 ? number_format($summary['profit'] / $summary['total'] * 100, 1) : 0 }}% ของยอดขาย</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-calculator"></i></div>
            <div><div class="stat-label">ยอดเฉลี่ยต่อบิล</div><div class="stat-value text-money">{{ number_format($summary['avg_bill'], 2) }}</div></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><i class="bi bi-bar-chart me-2"></i>แนวโน้มยอดขาย</div>
    <div class="card-body"><canvas id="reportChart" height="90"></canvas></div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-table me-2"></i>ตารางสรุปยอดขายรายวัน</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>วันที่</th><th class="text-center">จำนวนบิล</th>
                    <th class="text-end">ยอดก่อนส่วนลด</th><th class="text-end">ส่วนลด</th>
                    <th class="text-end">VAT</th><th class="text-end">ยอดขายสุทธิ</th>
                    <th class="text-end">เฉลี่ย/บิล</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daily as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ number_format($row->bills) }}</td>
                        <td class="text-end text-money">{{ number_format($row->subtotal, 2) }}</td>
                        <td class="text-end text-money text-danger">{{ number_format($row->discount, 2) }}</td>
                        <td class="text-end text-money text-muted">{{ number_format($row->vat, 2) }}</td>
                        <td class="text-end text-money fw-semibold">{{ number_format($row->total, 2) }}</td>
                        <td class="text-end text-money">{{ number_format($row->bills > 0 ? $row->total / $row->bills : 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">ไม่มีข้อมูลการขายในช่วงเวลาที่เลือก</td></tr>
                @endforelse
            </tbody>
            @if($daily->count())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td>รวมทั้งสิ้น</td>
                        <td class="text-center">{{ number_format($summary['bills']) }}</td>
                        <td class="text-end text-money">{{ number_format($summary['subtotal'], 2) }}</td>
                        <td class="text-end text-money">{{ number_format($summary['discount'], 2) }}</td>
                        <td class="text-end text-money">{{ number_format($summary['vat'], 2) }}</td>
                        <td class="text-end text-money">{{ number_format($summary['total'], 2) }}</td>
                        <td class="text-end text-money">{{ number_format($summary['avg_bill'], 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('reportChart'), {
        type: 'line',
        data: {
            labels: @json($daily->map(fn ($d) => \Carbon\Carbon::parse($d->date)->format('d/m'))),
            datasets: [{
                label: 'ยอดขายสุทธิ',
                data: @json($daily->pluck('total')),
                borderColor: '#1e40af',
                backgroundColor: 'rgba(30,64,175,.12)',
                fill: true,
                tension: .3,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('th-TH') } } }
        }
    });
</script>
@endpush
