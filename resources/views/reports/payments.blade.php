@extends('layouts.app')
@section('title', 'รายงานช่องทางการชำระเงิน')
@section('subtitle', 'ตั้งแต่ ' . \Carbon\Carbon::parse($from)->format('d/m/Y') . ' ถึง ' . \Carbon\Carbon::parse($to)->format('d/m/Y'))

@section('content')
@include('reports._filter')

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-list-check"></i></div>
            <div><div class="stat-label">จำนวนรายการรับชำระ</div><div class="stat-value">{{ number_format($summary['transactions']) }}</div></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-cash-stack"></i></div>
            <div><div class="stat-label">ยอดรับชำระรวม</div><div class="stat-value text-money">{{ number_format($summary['total'], 2) }}</div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart me-2"></i>สัดส่วนช่องทางการชำระ</div>
            <div class="card-body"><canvas id="payChart"></canvas></div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-table me-2"></i>สรุปแยกตามช่องทาง</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>ช่องทาง</th><th class="text-center">จำนวนรายการ</th>
                        <th class="text-end">ยอดรวม</th><th class="text-end">สัดส่วน</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td><i class="bi {{ ['cash'=>'bi-cash-stack','transfer'=>'bi-bank','promptpay'=>'bi-qr-code','credit_card'=>'bi-credit-card','e_wallet'=>'bi-wallet2'][$row->method] ?? 'bi-wallet2' }} me-2 text-primary"></i>
                                    {{ \App\Models\Payment::METHODS[$row->method] ?? $row->method }}</td>
                                <td class="text-center">{{ number_format($row->cnt) }}</td>
                                <td class="text-end text-money fw-semibold">{{ number_format($row->total, 2) }}</td>
                                <td class="text-end">{{ $summary['total'] > 0 ? number_format($row->total / $summary['total'] * 100, 1) : 0 }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-5">ไม่มีข้อมูลการรับชำระเงินในช่วงเวลาที่เลือก</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const methods = {{ \Illuminate\Support\Js::from($rows->map(fn ($r) => [
        'label' => \App\Models\Payment::METHODS[$r->method] ?? $r->method,
        'total' => (float) $r->total,
    ])) }};

    if (methods.length) {
        new Chart(document.getElementById('payChart'), {
            type: 'pie',
            data: {
                labels: methods.map(m => m.label),
                datasets: [{
                    data: methods.map(m => m.total),
                    backgroundColor: ['#1e40af','#0891b2','#16a34a','#ca8a04','#dc2626']
                }]
            },
            options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });
    }
</script>
@endpush
