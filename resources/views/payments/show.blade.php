@extends('layouts.app')
@section('title', 'รายละเอียดการชำระเงิน')
@section('subtitle', $payment->payment_no)

@section('content')
<div class="mb-3 d-flex gap-2">
    <a href="{{ route('payments.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>กลับ</a>
    <a href="{{ route('sales.show', $payment->sale) }}" class="btn btn-primary"><i class="bi bi-receipt me-1"></i>ดูบิล {{ $payment->sale->sale_no }}</a>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-cash-coin me-2"></i>ข้อมูลการชำระเงิน</div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">เลขที่ชำระ</span><span class="fw-semibold">{{ $payment->payment_no }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">ช่องทาง</span><span><i class="bi {{ $payment->method_icon }} me-1"></i>{{ $payment->method_name }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">จำนวนเงิน</span><span class="fw-bold text-money">{{ number_format($payment->amount, 2) }}</span></li>
                @if($payment->method === 'cash')
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">รับเงินมา</span><span class="text-money">{{ number_format($payment->received, 2) }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">เงินทอน</span><span class="text-money text-success">{{ number_format($payment->change_amount, 2) }}</span></li>
                @endif
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">เลขอ้างอิง</span><span>{{ $payment->reference_no ?? '-' }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">ผู้รับชำระ</span><span>{{ $payment->user->name ?? '-' }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">เวลาที่ชำระ</span><span>{{ $payment->paid_at?->format('d/m/Y H:i') }}</span></li>
            </ul>
            @if($payment->slip_image)
                <div class="card-body">
                    <div class="form-label">สลิปการโอนเงิน</div>
                    <img src="{{ asset('uploads/slips/'.$payment->slip_image) }}" class="img-fluid rounded border" alt="สลิป">
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-receipt me-2"></i>รายการสินค้าในบิล {{ $payment->sale->sale_no }}</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>สินค้า</th><th class="text-end">ราคา</th><th class="text-center">จำนวน</th><th class="text-end">รวม</th></tr></thead>
                    <tbody>
                        @foreach($payment->sale->items as $item)
                            <tr>
                                <td class="small">{{ $item->product_name }}</td>
                                <td class="text-end text-money">{{ number_format($item->price, 2) }}</td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end text-money">{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">ยอดสุทธิของบิล</th>
                            <th class="text-end text-money">{{ number_format($payment->sale->total, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
