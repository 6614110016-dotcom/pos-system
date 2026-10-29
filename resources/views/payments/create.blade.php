@extends('layouts.app')
@section('title', 'รับชำระเงิน')
@section('subtitle', 'บิลเลขที่ ' . $sale->sale_no)

@section('content')
<div class="row g-3">

    {{-- =============== สรุปรายการในบิล =============== --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-receipt me-2"></i>รายการสินค้าในบิล
                <span class="badge bg-{{ $sale->status_badge }} ms-auto">{{ $sale->status_name }}</span>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>สินค้า</th>
                            <th class="text-end">ราคา</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-end">รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            <tr>
                                <td>
                                    <div class="small fw-semibold">{{ $item->product_name }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $item->product_code }}</div>
                                </td>
                                <td class="text-end text-money">{{ number_format($item->price, 2) }}</td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end text-money fw-semibold">{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <div class="summary-line"><span class="text-muted">ลูกค้า</span><span>{{ $sale->customer_name }}</span></div>
                <div class="summary-line"><span class="text-muted">ยอดรวมก่อนส่วนลด</span><span class="text-money">{{ number_format($sale->subtotal, 2) }}</span></div>
                @if($sale->member_discount > 0)
                    <div class="summary-line text-success"><span>ส่วนลดสมาชิก</span><span class="text-money">-{{ number_format($sale->member_discount, 2) }}</span></div>
                @endif
                @if($sale->discount > 0)
                    <div class="summary-line text-success"><span>ส่วนลดท้ายบิล</span><span class="text-money">-{{ number_format($sale->discount, 2) }}</span></div>
                @endif
                <div class="summary-line text-muted" style="font-size:.85rem"><span>ภาษีมูลค่าเพิ่ม (รวมในราคา)</span><span class="text-money">{{ number_format($sale->vat, 2) }}</span></div>
                <div class="summary-total"><span>ยอดสุทธิ</span><span class="amount text-money">{{ number_format($sale->total, 2) }}</span></div>
            </div>
        </div>

        {{-- รายการที่ชำระมาแล้ว --}}
        @if($sale->payments->count())
            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-check2-circle me-2"></i>รายการที่ชำระแล้ว</div>
                <ul class="list-group list-group-flush">
                    @foreach($sale->payments as $p)
                        <li class="list-group-item d-flex align-items-center gap-2">
                            <i class="bi {{ $p->method_icon }} text-primary"></i>
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">{{ $p->method_name }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $p->payment_no }} &middot; {{ $p->paid_at?->format('d/m/Y H:i') }}</div>
                            </div>
                            <span class="text-money fw-semibold">{{ number_format($p->amount, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- =============== ฟอร์มรับชำระเงิน =============== --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-cash-coin me-2"></i>รับชำระเงิน</div>
            <div class="card-body">

                <div class="alert alert-primary d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">ยอดที่ต้องชำระ</span>
                    <span class="fs-3 fw-bold text-money" id="outstandingText">{{ number_format($sale->outstanding, 2) }}</span>
                </div>

                <form method="POST" action="{{ route('payments.store', $sale) }}" enctype="multipart/form-data" id="paymentForm">
                    @csrf

                    {{-- เลือกช่องทาง --}}
                    <label class="form-label required">ช่องทางการชำระเงิน</label>
                    <div class="row g-2 mb-3">
                        @foreach($methods as $key => $label)
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="method" id="m_{{ $key }}"
                                       value="{{ $key }}" @checked($loop->first) onchange="toggleMethod()">
                                <label class="btn btn-outline-primary w-100 text-start py-2" for="m_{{ $key }}">
                                    <i class="bi {{ ['cash'=>'bi-cash-stack','transfer'=>'bi-bank','promptpay'=>'bi-qr-code','credit_card'=>'bi-credit-card','e_wallet'=>'bi-wallet2'][$key] }} me-1"></i>
                                    <span class="small">{{ $label }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">จำนวนเงินที่ชำระ</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amountInput"
                               class="form-control form-control-lg text-end text-money"
                               value="{{ number_format($sale->outstanding, 2, '.', '') }}"
                               max="{{ $sale->outstanding }}" required oninput="calcChange()">
                        <div class="form-text">ชำระน้อยกว่ายอดรวมได้ ระบบจะบันทึกเป็นการชำระบางส่วน</div>
                    </div>

                    {{-- เงินสด --}}
                    <div id="cashBox">
                        <div class="mb-2">
                            <label class="form-label">รับเงินมา</label>
                            <input type="number" step="0.01" min="0" name="received" id="receivedInput"
                                   class="form-control form-control-lg text-end text-money"
                                   value="{{ number_format($sale->outstanding, 2, '.', '') }}" oninput="calcChange()">
                        </div>

                        {{-- ปุ่มธนบัตรลัด --}}
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            @foreach([20, 50, 100, 500, 1000] as $note)
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addCash({{ $note }})">
                                    +{{ number_format($note) }}
                                </button>
                            @endforeach
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="exactCash()">พอดี</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="resetCash()">ล้าง</button>
                        </div>

                        <div class="alert alert-success d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">เงินทอน</span>
                            <span class="fs-4 fw-bold text-money" id="changeText">0.00</span>
                        </div>
                    </div>

                    {{-- โอน / บัตร --}}
                    <div id="refBox" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">เลขที่อ้างอิง / เลขอนุมัติ</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="เช่น เลขที่รายการโอน 6 หลักท้าย">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">แนบสลิป (ถ้ามี)</label>
                            <input type="file" name="slip" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-lg flex-grow-1 fw-semibold">
                            <i class="bi bi-check-circle me-1"></i> ยืนยันการชำระเงิน
                        </button>
                        <a href="{{ route('pos.index') }}" class="btn btn-light btn-lg">กลับหน้าขาย</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const outstanding = {{ $sale->outstanding }};

    function isCash() {
        return document.querySelector('input[name="method"]:checked').value === 'cash';
    }

    function toggleMethod() {
        document.getElementById('cashBox').classList.toggle('d-none', !isCash());
        document.getElementById('refBox').classList.toggle('d-none', isCash());
        calcChange();
    }

    function calcChange() {
        const amount   = parseFloat(document.getElementById('amountInput').value || 0);
        const received = parseFloat(document.getElementById('receivedInput').value || 0);
        const change   = isCash() ? Math.max(0, received - amount) : 0;
        document.getElementById('changeText').textContent =
            change.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function addCash(v) {
        const el = document.getElementById('receivedInput');
        el.value = (parseFloat(el.value || 0) + v).toFixed(2);
        calcChange();
    }

    function exactCash() {
        document.getElementById('receivedInput').value = parseFloat(document.getElementById('amountInput').value || 0).toFixed(2);
        calcChange();
    }

    function resetCash() {
        document.getElementById('receivedInput').value = '0.00';
        calcChange();
    }

    toggleMethod();
</script>
@endpush
