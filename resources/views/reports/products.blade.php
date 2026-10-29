@extends('layouts.app')
@section('title', 'รายงานสินค้าขายดี')
@section('subtitle', 'ตั้งแต่ ' . \Carbon\Carbon::parse($from)->format('d/m/Y') . ' ถึง ' . \Carbon\Carbon::parse($to)->format('d/m/Y'))

@section('content')
@include('reports._filter')

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-box-seam"></i></div>
            <div><div class="stat-label">สินค้าที่ขายได้</div><div class="stat-value">{{ number_format($summary['items']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-boxes"></i></div>
            <div><div class="stat-label">จำนวนชิ้นที่ขาย</div><div class="stat-value">{{ number_format($summary['qty']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-cash-stack"></i></div>
            <div><div class="stat-label">ยอดขายรวม</div><div class="stat-value text-money">{{ number_format($summary['amount'], 2) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-graph-up-arrow"></i></div>
            <div><div class="stat-label">กำไรรวม</div><div class="stat-value text-money">{{ number_format($summary['profit'], 2) }}</div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart me-2"></i>สัดส่วนยอดขาย 8 อันดับแรก</div>
            <div class="card-body"><canvas id="pieChart"></canvas></div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-trophy me-2"></i>อันดับสินค้าขายดี</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th class="text-center">อันดับ</th><th>สินค้า</th>
                            <th class="text-center">จำนวนที่ขาย</th><th class="text-end">ยอดขาย</th><th class="text-end">กำไร</th></tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-{{ $i < 3 ? ['warning','secondary','danger'][$i] : 'light text-dark' }}">{{ $i + 1 }}</span>
                                </td>
                                <td>
                                    <div class="small fw-semibold">{{ $row->product_name }}</div>
                                    <div class="text-muted" style="font-size:.72rem">{{ $row->product_code }}</div>
                                </td>
                                <td class="text-center fw-semibold">{{ number_format($row->total_qty) }}</td>
                                <td class="text-end text-money">{{ number_format($row->total_amount, 2) }}</td>
                                <td class="text-end text-money text-success">{{ number_format($row->profit, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">ไม่มีข้อมูลการขายในช่วงเวลาที่เลือก</td></tr>
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
    const top = @json($rows->take(8)->values());

    if (top.length) {
        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: top.map(r => r.product_name),
                datasets: [{
                    data: top.map(r => parseFloat(r.total_amount)),
                    backgroundColor: ['#1e40af','#0891b2','#16a34a','#ca8a04','#dc2626','#7c3aed','#db2777','#0f766e']
                }]
            },
            options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });
    }
</script>
@endpush
