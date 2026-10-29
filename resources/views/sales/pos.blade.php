@extends('layouts.app')
@section('title', 'ขายสินค้า (POS)')
@section('subtitle', 'เลือกสินค้าลงตะกร้า ผูกข้อมูลสมาชิก แล้วกดเปิดบิลเพื่อรับชำระเงิน')

@section('content')
<div class="row g-3">

    {{-- =============== ฝั่งซ้าย: เลือกสินค้า =============== --}}
    <div class="col-lg-8">

        {{-- ค้นหา / ยิงบาร์โค้ด --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-5">
                        <form method="POST" action="{{ route('pos.cart.add') }}" id="barcodeForm">
                            @csrf
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" name="barcode" id="barcodeInput" class="form-control"
                                       placeholder="ยิงบาร์โค้ด / พิมพ์รหัสสินค้า แล้วกด Enter" autofocus>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <form method="GET" action="{{ route('pos.index') }}">
                            <div class="input-group">
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                                       placeholder="ค้นหาชื่อสินค้า...">
                                <select name="category_id" class="form-select" style="max-width:190px">
                                    <option value="">ทุกหมวดหมู่</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
                                @if(request()->hasAny(['q', 'category_id']))
                                    <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- รายการสินค้า --}}
        <div class="row g-2 row-cols-2 row-cols-md-3 row-cols-xl-4">
            @forelse($products as $product)
                <div class="col">
                    <form method="POST" action="{{ route('pos.cart.add') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" class="pos-product w-100 border-0 {{ $product->stock_qty <= 0 ? 'disabled' : '' }}"
                                {{ $product->stock_qty <= 0 ? 'disabled' : '' }}>
                            <img src="{{ $product->image_url }}" class="p-thumb" alt="{{ $product->name }}"
                                 onerror="this.src='{{ asset('images/no-image.svg') }}'">
                            <div class="p-name">{{ $product->name }}</div>
                            <div class="p-price text-money">{{ number_format($product->price, 2) }} ฿</div>
                            <div class="p-stock">
                                คงเหลือ
                                <span class="badge bg-{{ $product->stock_badge }}">{{ $product->stock_qty }}</span>
                                {{ $product->unit }}
                            </div>
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-12">
                    <div class="card"><div class="card-body text-center text-muted py-5">
                        <i class="bi bi-search fs-1 d-block mb-2 opacity-50"></i>
                        ไม่พบสินค้าตามเงื่อนไขที่ค้นหา
                    </div></div>
                </div>
            @endforelse
        </div>

        <div class="mt-3">{{ $products->links() }}</div>
    </div>

    {{-- =============== ฝั่งขวา: ตะกร้าสินค้า =============== --}}
    <div class="col-lg-4">
        <div class="card cart-panel">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-basket3 me-2"></i>ตะกร้าสินค้า
                <span class="badge bg-primary ms-2">{{ $summary['total_qty'] }} ชิ้น</span>
                @if(!empty($items))
                    <form method="POST" action="{{ route('pos.cart.clear') }}" class="ms-auto"
                          data-confirm="ต้องการล้างสินค้าทั้งหมดในตะกร้าใช่หรือไม่?">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                @endif
            </div>

            {{-- สมาชิก --}}
            <div class="px-3 py-2 border-bottom bg-light">
                @if($member)
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-check-fill text-success fs-5"></i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate small">{{ $member->name }}</div>
                            <div class="text-muted" style="font-size:.75rem">
                                {{ $member->member_code }} &middot;
                                <span class="badge bg-{{ $member->tier_badge }}">{{ $member->tier_name }}</span>
                                ลด {{ $member->discount_rate }}% &middot; {{ number_format($member->points) }} แต้ม
                            </div>
                        </div>
                        <form method="POST" action="{{ route('pos.member') }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary" title="ยกเลิกสมาชิก"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </div>
                @else
                    <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#memberModal">
                        <i class="bi bi-person-plus me-1"></i> เลือกสมาชิก (ลูกค้าทั่วไป)
                    </button>
                @endif
            </div>

            {{-- รายการในตะกร้า --}}
            <div class="card-body cart-items py-2">
                @forelse($items as $item)
                    <div class="cart-row">
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold small text-truncate">{{ $item['name'] }}</div>
                            <div class="text-muted" style="font-size:.75rem">
                                {{ number_format($item['price'], 2) }} ฿ /{{ $item['unit'] }}
                                @if($item['discount'] > 0)
                                    <span class="text-danger">- ลด {{ number_format($item['discount'], 2) }}</span>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-1 mt-1">
                                <form method="POST" action="{{ route('pos.cart.update') }}" class="d-flex align-items-center gap-1">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2"
                                            onclick="stepQty(this, -1)">&minus;</button>
                                    <input type="number" name="qty" value="{{ $item['qty'] }}" min="1" max="{{ $item['stock'] }}"
                                           class="form-control form-control-sm text-center px-1" style="width:56px"
                                           onchange="this.form.submit()">
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2"
                                            onclick="stepQty(this, 1)">+</button>
                                </form>

                                <button class="btn btn-sm btn-outline-warning py-0 px-2" title="ส่วนลดรายการ"
                                        data-bs-toggle="modal" data-bs-target="#itemDiscountModal"
                                        data-id="{{ $item['product_id'] }}" data-name="{{ $item['name'] }}"
                                        data-discount="{{ $item['discount'] }}">
                                    <i class="bi bi-percent"></i>
                                </button>

                                <form method="POST" action="{{ route('pos.cart.remove') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="text-end fw-semibold text-money" style="min-width:78px">
                            {{ number_format(($item['price'] * $item['qty']) - $item['discount'], 2) }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-basket fs-1 d-block mb-2 opacity-50"></i>
                        ยังไม่มีสินค้าในตะกร้า<br>
                        <small>คลิกที่สินค้าด้านซ้ายเพื่อเพิ่มลงตะกร้า</small>
                    </div>
                @endforelse
            </div>

            {{-- สรุปยอด --}}
            <div class="card-footer bg-white">
                <div class="summary-line">
                    <span class="text-muted">ยอดรวม ({{ $summary['total_qty'] }} ชิ้น)</span>
                    <span class="text-money">{{ number_format($summary['subtotal'], 2) }}</span>
                </div>

                @if($summary['member_discount'] > 0)
                    <div class="summary-line text-success">
                        <span>ส่วนลดสมาชิก ({{ $summary['member_rate'] }}%)</span>
                        <span class="text-money">-{{ number_format($summary['member_discount'], 2) }}</span>
                    </div>
                @endif

                <div class="summary-line align-items-center">
                    <span class="text-muted">ส่วนลดท้ายบิล</span>
                    <form method="POST" action="{{ route('pos.discount') }}" class="d-flex gap-1">
                        @csrf
                        <input type="number" step="0.01" min="0" name="discount" value="{{ $summary['discount'] }}"
                               class="form-control form-control-sm text-end" style="width:92px">
                        <button class="btn btn-sm btn-outline-primary"><i class="bi bi-check-lg"></i></button>
                    </form>
                </div>

                <div class="summary-line text-muted" style="font-size:.82rem">
                    <span>ภาษีมูลค่าเพิ่ม {{ $summary['vat_rate'] }}% (รวมในราคาแล้ว)</span>
                    <span class="text-money">{{ number_format($summary['vat'], 2) }}</span>
                </div>

                <div class="summary-total">
                    <span>ยอดสุทธิ</span>
                    <span class="amount text-money">{{ number_format($summary['total'], 2) }}</span>
                </div>

                @if($summary['points_earned'] > 0)
                    <div class="text-end small text-success mt-1">
                        <i class="bi bi-star-fill"></i> สมาชิกจะได้รับ {{ $summary['points_earned'] }} แต้ม
                    </div>
                @endif

                <form method="POST" action="{{ route('pos.checkout') }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="note" id="saleNote">
                    <button class="btn btn-primary w-100 py-2 fw-semibold" {{ empty($items) ? 'disabled' : '' }}>
                        <i class="bi bi-cash-coin me-1"></i> เปิดบิล / ชำระเงิน (F9)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- =============== Modal: ค้นหาสมาชิก =============== --}}
<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-people me-2"></i>เลือกสมาชิก</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="memberSearch" class="form-control" placeholder="ค้นหาจากชื่อ / เบอร์โทร / รหัสสมาชิก">
                </div>
                <div id="memberResults" class="list-group">
                    <div class="text-center text-muted py-4 small">พิมพ์เพื่อค้นหาสมาชิก</div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('members.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-person-plus me-1"></i>สมัครสมาชิกใหม่
                </a>
                <button class="btn btn-light btn-sm" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

{{-- =============== Modal: ส่วนลดรายการ =============== --}}
<div class="modal fade" id="itemDiscountModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form method="POST" action="{{ route('pos.cart.item-discount') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fs-6">ส่วนลดรายการ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="product_id" id="discountProductId">
                <p class="small text-muted mb-2" id="discountProductName"></p>
                <label class="form-label">ส่วนลด (บาท)</label>
                <input type="number" step="0.01" min="0" name="discount" id="discountAmount" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary btn-sm">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('pos.member') }}" id="pickMemberForm" class="d-none">
    @csrf
    <input type="hidden" name="member_id" id="pickMemberId">
</form>
@endsection

@push('scripts')
<script>
    // ---------- ค้นหาสมาชิกแบบ AJAX ----------
    let searchTimer = null;
    const memberSearch  = document.getElementById('memberSearch');
    const memberResults = document.getElementById('memberResults');

    memberSearch.addEventListener('input', function () {
        clearTimeout(searchTimer);
        const term = this.value.trim();

        if (term.length < 2) {
            memberResults.innerHTML = '<div class="text-center text-muted py-4 small">พิมพ์อย่างน้อย 2 ตัวอักษร</div>';
            return;
        }

        searchTimer = setTimeout(function () {
            fetch('{{ route('pos.search-member') }}?q=' + encodeURIComponent(term))
                .then(r => r.json())
                .then(function (data) {
                    if (!data.length) {
                        memberResults.innerHTML = '<div class="text-center text-muted py-4 small">ไม่พบสมาชิกที่ค้นหา</div>';
                        return;
                    }
                    memberResults.innerHTML = data.map(m => `
                        <button type="button" class="list-group-item list-group-item-action" onclick="pickMember(${m.id})">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold">${m.name}</span>
                                <span class="badge bg-secondary">${m.tier}</span>
                            </div>
                            <small class="text-muted">${m.member_code} · ${m.phone} · ${m.points} แต้ม · ลด ${m.discount}%</small>
                        </button>`).join('');
                });
        }, 300);
    });

    function pickMember(id) {
        document.getElementById('pickMemberId').value = id;
        document.getElementById('pickMemberForm').submit();
    }

    // ---------- ปุ่มเพิ่ม/ลดจำนวนในตะกร้า ----------
    function stepQty(btn, step) {
        const form  = btn.closest('form');
        const input = form.querySelector('input[name="qty"]');
        const next  = parseInt(input.value || '1', 10) + step;
        input.value = next < 0 ? 0 : next;
        form.submit();
    }

    // ---------- Modal ส่วนลดรายการ ----------
    document.getElementById('itemDiscountModal').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        document.getElementById('discountProductId').value  = btn.dataset.id;
        document.getElementById('discountProductName').textContent = btn.dataset.name;
        document.getElementById('discountAmount').value     = btn.dataset.discount;
    });

    // ---------- คีย์ลัด ----------
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F9') {                                  // F9 = เปิดบิล
            e.preventDefault();
            document.querySelector('form[action="{{ route('pos.checkout') }}"]')?.submit();
        }
        if (e.key === 'F2') {                                  // F2 = โฟกัสช่องบาร์โค้ด
            e.preventDefault();
            document.getElementById('barcodeInput').focus();
        }
    });

    // เคอร์เซอร์กลับไปที่ช่องบาร์โค้ดเสมอ
    window.addEventListener('load', () => document.getElementById('barcodeInput').focus());
</script>
@endpush
