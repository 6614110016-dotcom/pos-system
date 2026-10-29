@extends('layouts.app')
@section('title', 'โมดูลรายงาน')
@section('subtitle', 'เลือกรายงานที่ต้องการดู พร้อมกำหนดช่วงวันที่ได้เอง')

@section('content')
<div class="row g-3">
    @php
        $reports = [
            ['route' => 'reports.sales',     'icon' => 'bi-graph-up',        'color' => 'primary', 'title' => 'รายงานยอดขายรายวัน',  'desc' => 'สรุปยอดขาย ส่วนลด ภาษี และกำไรขั้นต้นแยกตามวัน'],
            ['route' => 'reports.products',  'icon' => 'bi-box-seam',        'color' => 'success', 'title' => 'รายงานสินค้าขายดี',    'desc' => 'จัดอันดับสินค้าตามจำนวนที่ขายได้และกำไรที่ทำได้'],
            ['route' => 'reports.employees', 'icon' => 'bi-person-badge',    'color' => 'warning', 'title' => 'รายงานยอดขายตามพนักงาน', 'desc' => 'เปรียบเทียบผลงานการขายของพนักงานแต่ละคน'],
            ['route' => 'reports.members',   'icon' => 'bi-people',          'color' => 'info',    'title' => 'รายงานสมาชิก',        'desc' => 'ยอดซื้อสะสม แต้มสะสม และสมาชิกใหม่ในช่วงเวลา'],
            ['route' => 'reports.stock',     'icon' => 'bi-boxes',           'color' => 'danger',  'title' => 'รายงานสต็อกคงเหลือ',   'desc' => 'จำนวนคงเหลือ มูลค่าสต็อก และสินค้าที่ต้องสั่งเพิ่ม'],
            ['route' => 'reports.payments',  'icon' => 'bi-credit-card',     'color' => 'secondary','title' => 'รายงานช่องทางชำระเงิน', 'desc' => 'สัดส่วนการรับชำระเงินสด โอน พร้อมเพย์ และบัตร'],
        ];
    @endphp

    @foreach($reports as $r)
        <div class="col-md-6 col-xl-4">
            <a href="{{ route($r['route']) }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="stat-icon bg-{{ $r['color'] }} bg-opacity-10 text-{{ $r['color'] }} mb-3">
                            <i class="bi {{ $r['icon'] }}"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">{{ $r['title'] }}</h6>
                        <p class="small text-muted mb-0">{{ $r['desc'] }}</p>
                    </div>
                    <div class="card-footer bg-white text-{{ $r['color'] }} small fw-semibold">
                        ดูรายงาน <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
