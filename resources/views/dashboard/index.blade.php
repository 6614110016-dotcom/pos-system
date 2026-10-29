@extends('layouts.app')
@section('title', 'แดชบอร์ด')
@section('subtitle', 'ภาพรวมการขายและสถานะร้าน ณ วันที่ ' . now()->format('d/m/') . (now()->year + 543))

@section('content')

{{-- ============ การ์ดสรุปยอด ============ --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-label">ยอดขายวันนี้</div>
                <div class="stat-value text-money">{{ number_format($stats['today_total'], 2) }}</div>
                <div class="small text-muted">{{ number_format($stats['today_bills']) }} บิล</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="stat-label">ยอดขายเดือนนี้</div>
                <div class="stat-value text-money">{{ number_format($stats['month_total'], 2) }}</div>
                <div class="small text-muted">เฉลี่ย/บิลวันนี้ {{ number_format($stats['avg_bill'], 2) }} บาท</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="stat-label">สินค้าที่เปิดขาย</div>
                <div class="stat-value">{{ number_format($stats['products']) }}</div>
                <div class="small text-muted">สมาชิก {{ number_format($stats['members']) }} คน</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="stat-label">สินค้าใกล้หมด</div>
                <div class="stat-value">{{ number_format($stats['low_stock']) }}</div>
                <div class="small text-muted">หมดสต็อก {{ $stats['out_of_stock'] }} รายการ</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- ============ กราฟยอดขาย 7 วัน ============ --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-bar-chart-line me-2"></i>ยอดขาย 7 วันล่าสุด
                <a href="{{ route('pos.index') }}" class="btn btn-sm btn-primary ms-auto">
                    <i class="bi bi-basket3 me-1"></i>เปิดหน้าจอขาย
                </a>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="110"></canvas>
            </div>
        </div>
    </div>

    {{-- ============ สินค้าขายดี ============ --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-trophy me-2"></i>สินค้าขายดีเดือนนี้</div>
            <div class="card-body p-0">
                @forelse($topProducts as $i => $p)
                    <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                        <span class="badge bg-{{ ['warning','secondary','danger','light text-dark','light text-dark'][$i] ?? 'light text-dark' }} rounded-circle" style="width:26px;height:26px;line-height:18px;">{{ $i + 1 }}</span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="text-truncate small fw-semibold">{{ $p->product_name }}</div>
                            <div class="text-muted" style="font-size:.78rem">ขายได้ {{ number_format($p->total_qty) }} หน่วย</div>
                        </div>
                        <div class="text-end small fw-semibold text-money">{{ number_format($p->total_amount, 0) }}</div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        ยังไม่มีข้อมูลการขายในเดือนนี้
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ============ บิลล่าสุด ============ --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-receipt me-2"></i>บิลล่าสุด
                <a href="{{ route('sales.index') }}" class="ms-auto small text-decoration-none">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>เลขที่บิล</th>
                            <th>ลูกค้า</th>
                            <th>พนักงาน</th>
                            <th class="text-end">ยอดสุทธิ</th>
                            <th class="text-center">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                            <tr>
                                <td><a href="{{ route('sales.show', $sale) }}" class="text-decoration-none fw-semibold">{{ $sale->sale_no }}</a></td>
                                <td class="text-truncate" style="max-width:140px">{{ $sale->customer_name }}</td>
                                <td class="small text-muted">{{ $sale->user->name ?? '-' }}</td>
                                <td class="text-end text-money fw-semibold">{{ number_format($sale->total, 2) }}</td>
                                <td class="text-center"><span class="badge bg-{{ $sale->status_badge }}">{{ $sale->status_name }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีรายการขาย</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============ สินค้าใกล้หมด ============ --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>สินค้าที่ควรสั่งเพิ่ม
                <a href="{{ route('stock.index', ['filter' => 'low']) }}" class="ms-auto small text-decoration-none">จัดการสต็อก <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>สินค้า</th><th class="text-center">คงเหลือ</th><th class="text-center">จุดสั่งซื้อ</th></tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockProducts as $p)
                            <tr>
                                <td>
                                    <div class="text-truncate small fw-semibold" style="max-width:180px">{{ $p->name }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $p->product_code }}</div>
                                </td>
                                <td class="text-center"><span class="badge bg-{{ $p->stock_badge }}">{{ $p->stock_qty }}</span></td>
                                <td class="text-center text-muted small">{{ $p->reorder_level }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-success py-4"><i class="bi bi-check-circle me-1"></i>สต็อกสินค้าเพียงพอทุกรายการ</td></tr>
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
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'ยอดขาย (บาท)',
                data: @json($chartValues),
                backgroundColor: 'rgba(30, 64, 175, .75)',
                borderRadius: 6,
                maxBarThickness: 46
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.y.toLocaleString('th-TH', { minimumFractionDigits: 2 }) + ' บาท'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => v.toLocaleString('th-TH') }
                },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush
