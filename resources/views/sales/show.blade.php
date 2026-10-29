@extends('layouts.app')
@section('title', 'รายละเอียดบิลขาย')
@section('subtitle', $sale->sale_no)

@section('content')
<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="{{ route('sales.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>กลับ</a>
    @if($sale->status === 'paid')
        <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-primary"><i class="bi bi-printer me-1"></i>พิมพ์ใบเสร็จ</a>
    @elseif($sale->status === 'pending')
        <a href="{{ route('payments.create', $sale) }}" class="btn btn-success"><i class="bi bi-cash-coin me-1"></i>รับชำระเงิน</a>
    @endif
    @if($sale->status !== 'void' && auth()->user()->hasRole('admin', 'manager'))
        <button class="btn btn-outline-danger ms-auto" data-bs-toggle="modal" data-bs-target="#voidModal">
            <i class="bi bi-x-octagon me-1"></i>ยกเลิกบิล
        </button>
    @endif
</div>

@if($sale->status === 'void')
    <div class="alert alert-danger">
        <strong><i class="bi bi-x-octagon me-1"></i>บิลนี้ถูกยกเลิกแล้ว</strong><br>
        <span class="small">
            ยกเลิกโดย {{ $sale->voidedBy->name ?? '-' }}
            เมื่อ {{ $sale->voided_at?->format('d/m/Y H:i') }}
            &middot; เหตุผล: {{ $sale->void_reason }}
        </span>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-box-seam me-2"></i>รายการสินค้า</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th><th>สินค้า</th>
                            <th class="text-end">ราคา</th><th class="text-center">จำนวน</th>
                            <th class="text-end">ส่วนลด</th><th class="text-end">รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $i => $item)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-semibold small">{{ $item->product_name }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $item->product_code }}</div>
                                </td>
                                <td class="text-end text-money">{{ number_format($item->price, 2) }}</td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end text-money text-danger">{{ $item->discount > 0 ? '-'.number_format($item->discount, 2) : '-' }}</td>
                                <td class="text-end text-money fw-semibold">{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-cash-coin me-2"></i>การชำระเงิน</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr><th>เลขที่</th><th>ช่องทาง</th><th>อ้างอิง</th><th>ผู้รับชำระ</th><th>เวลา</th><th class="text-end">จำนวนเงิน</th></tr>
                    </thead>
                    <tbody>
                        @forelse($sale->payments as $p)
                            <tr>
                                <td class="small fw-semibold">{{ $p->payment_no }}</td>
                                <td class="small"><i class="bi {{ $p->method_icon }} me-1"></i>{{ $p->method_name }}</td>
                                <td class="small text-muted">{{ $p->reference_no ?? '-' }}</td>
                                <td class="small text-muted">{{ $p->user->name ?? '-' }}</td>
                                <td class="small text-muted">{{ $p->paid_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-end text-money fw-semibold">{{ number_format($p->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีการชำระเงิน</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>ข้อมูลบิล</div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">เลขที่บิล</span><span class="fw-semibold">{{ $sale->sale_no }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">สถานะ</span><span class="badge bg-{{ $sale->status_badge }}">{{ $sale->status_name }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">วันที่เปิดบิล</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">วันที่ชำระ</span><span>{{ $sale->sold_at?->format('d/m/Y H:i') ?? '-' }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">พนักงานขาย</span><span>{{ $sale->user->name ?? '-' }}</span></li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">ลูกค้า</span>
                    <span>
                        @if($sale->member)
                            <a href="{{ route('members.show', $sale->member) }}" class="text-decoration-none">{{ $sale->member->name }}</a>
                        @else ลูกค้าทั่วไป @endif
                    </span>
                </li>
                @if($sale->note)
                    <li class="list-group-item"><span class="text-muted">หมายเหตุ:</span> {{ $sale->note }}</li>
                @endif
            </ul>
            <div class="card-footer bg-white">
                <div class="summary-line"><span class="text-muted">ยอดรวม</span><span class="text-money">{{ number_format($sale->subtotal, 2) }}</span></div>
                <div class="summary-line text-success"><span>ส่วนลดสมาชิก</span><span class="text-money">-{{ number_format($sale->member_discount, 2) }}</span></div>
                <div class="summary-line text-success"><span>ส่วนลดท้ายบิล</span><span class="text-money">-{{ number_format($sale->discount, 2) }}</span></div>
                <div class="summary-line text-muted" style="font-size:.85rem"><span>VAT (รวมในราคา)</span><span class="text-money">{{ number_format($sale->vat, 2) }}</span></div>
                <div class="summary-total"><span>ยอดสุทธิ</span><span class="amount text-money">{{ number_format($sale->total, 2) }}</span></div>
                @if($sale->outstanding > 0 && $sale->status !== 'void')
                    <div class="alert alert-warning mt-2 mb-0 py-2 small d-flex justify-content-between">
                        <span>ค้างชำระ</span><span class="fw-bold text-money">{{ number_format($sale->outstanding, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        @if(auth()->user()->hasRole('admin', 'manager'))
            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-graph-up me-2"></i>กำไรขั้นต้นของบิล</div>
                <div class="card-body text-center">
                    <div class="stat-value text-success text-money">{{ number_format($sale->profit, 2) }}</div>
                    <div class="small text-muted">
                        คิดเป็น {{ $sale->total > 0 ? number_format($sale->profit / $sale->total * 100, 1) : 0 }}% ของยอดขาย
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modal ยกเลิกบิล --}}
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('sales.void', $sale) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="bi bi-x-octagon me-2"></i>ยกเลิกบิล {{ $sale->sale_no }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small">
                    การยกเลิกบิลจะ <strong>คืนสต็อกสินค้าทุกรายการ</strong> และคืนแต้มสะสมของสมาชิก (ถ้ามี) โดยไม่สามารถย้อนกลับได้
                </div>
                <label class="form-label required">เหตุผลในการยกเลิก</label>
                <textarea name="void_reason" rows="3" class="form-control" required
                          placeholder="เช่น ลูกค้าเปลี่ยนใจ / คีย์รายการผิด"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิด</button>
                <button class="btn btn-danger">ยืนยันยกเลิกบิล</button>
            </div>
        </form>
    </div>
</div>
@endsection
