<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'หน้าหลัก') | {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<div class="app-wrapper">

    {{-- ============ แถบเมนูด้านซ้าย ============ --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-shop"></i>
            <div>
                <div class="brand-title">POS SYSTEM</div>
                <div class="brand-sub">{{ \App\Models\Setting::get('shop_name', config('app.shop.name')) }}</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">ภาพรวม</div>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> แดชบอร์ด
            </a>

            <div class="nav-section">การขาย</div>
            <a href="{{ route('pos.index') }}" class="nav-item {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="bi bi-basket3"></i> ขายสินค้า (POS)
                @if($posCartCount = app(\App\Services\PosCart::class)->count())
                    <span class="badge bg-warning text-dark ms-auto">{{ $posCartCount }}</span>
                @endif
            </a>
            <a href="{{ route('sales.index') }}" class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> ประวัติการขาย
            </a>
            <a href="{{ route('payments.index') }}" class="nav-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i> รายการรับชำระเงิน
            </a>

            <div class="nav-section">ข้อมูลหลัก</div>
            <a href="{{ route('products.index') }}" class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> สินค้า
            </a>
            <a href="{{ route('categories.index') }}" class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> หมวดหมู่สินค้า
            </a>
            <a href="{{ route('stock.index') }}" class="nav-item {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i> จัดการสต็อก
                @php $lowStock = \App\Models\Product::active()->lowStock()->count(); @endphp
                @if($lowStock)
                    <span class="badge bg-danger ms-auto">{{ $lowStock }}</span>
                @endif
            </a>
            <a href="{{ route('members.index') }}" class="nav-item {{ request()->routeIs('members.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> สมาชิก
            </a>

            @if(auth()->user()->hasRole('admin', 'manager'))
                <div class="nav-section">จัดการระบบ</div>
                <a href="{{ route('employees.index') }}" class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i> พนักงาน
                </a>
                <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i> รายงาน
                </a>
            @endif

            @if(auth()->user()->isAdmin())
                <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> ตั้งค่าระบบ
                </a>
                <a href="{{ route('settings.logs') }}" class="nav-item {{ request()->routeIs('settings.logs') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> บันทึกการใช้งาน
                </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="small text-white-50">เวอร์ชัน 1.0 &middot; Laravel {{ app()->version() }}</div>
        </div>
    </aside>

    {{-- ============ ส่วนเนื้อหา ============ --}}
    <div class="main-area">
        <header class="topbar">
            <button class="btn btn-sm btn-light d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>

            <div class="page-title">
                <h1>@yield('title', 'หน้าหลัก')</h1>
                @hasSection('subtitle')
                    <p>@yield('subtitle')</p>
                @endif
            </div>

            <div class="topbar-right">
                <span class="clock" id="clock"></span>
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                        <span class="d-none d-md-inline text-start lh-1">
                            <span class="d-block fw-semibold">{{ auth()->user()->name }}</span>
                            <small class="text-muted">{{ auth()->user()->role_name }}</small>
                        </span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-semibold">{{ auth()->user()->employee_code }}</div>
                            <small class="text-muted">{{ auth()->user()->email }}</small>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('password.change') }}"><i class="bi bi-key me-2"></i>เปลี่ยนรหัสผ่าน</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content">
            @include('partials.alerts')
            @yield('content')
        </main>

        <footer class="app-footer">
            ระบบขายหน้าร้าน (POS) &copy; {{ date('Y') + 543 }} &middot; พัฒนาด้วย Laravel Framework
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // นาฬิกาบนแถบด้านบน
    function tickClock() {
        const now = new Date();
        const el = document.getElementById('clock');
        if (el) {
            el.textContent = now.toLocaleDateString('th-TH', { day:'2-digit', month:'short', year:'numeric' })
                + ' ' + now.toLocaleTimeString('th-TH', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
        }
    }
    tickClock(); setInterval(tickClock, 1000);

    // ยืนยันก่อนลบข้อมูล
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        });
    });
</script>
@stack('scripts')
</body>
</html>
