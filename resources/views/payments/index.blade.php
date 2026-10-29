@extends('layouts.app')
@section('title', 'รายการรับชำระเงิน')
@section('subtitle', 'ตรวจสอบการรับชำระเงินทุกช่องทาง')

@section('content')

<div class="row g-3 mb-3">
    @foreach($byMethod as $m)
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi {{ ['cash'=>'bi-cash-stack','transfer'=>'bi-bank','promptpay'=>'bi-qr-code','credit_card'=>'bi-credit-card','e_wallet'=>'bi-wallet2'][$m->method] ?? 'bi-wallet2' }}"></i>
                </div>
                <div>
                    <div class="stat-label">{{ \App\Models\Payment::METHODS[$m->method] ?? $m->method }}</div>
                    <div class="stat-value text-money">{{ number_format($m->total, 0) }}</div>
                    <div class="small text-muted">{{ number_format($m->cnt) }} รายการ</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="ค้นหาเลขที่ชำระ / เลขที่บิล">
            </div>
            <div class="col-md-3">
                <select name="method" class="form-select">
                    <option value="">ทุกช่องทาง</option>
                    @foreach(\App\Models\Payment::METHODS as $key => $label)
                        <option value="{{ $key }}" @selected(request('method') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
            <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search"></i></button>
                <a href="{{ route('payments.index') }}" class="btn btn-light">ล้าง</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>เลขที่ชำระ</th><th>บิล</th><th>ลูกค้า</th>
                    <th class="text-center">ช่องทาง</th><th>อ้างอิง</th>
                    <th>ผู้รับชำระ</th><th>เวลา</th>
                    <th class="text-end">จำนวนเงิน</th><th class="text-center">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td class="fw-semibold small">{{ $p->payment_no }}</td>
                        <td><a href="{{ route('sales.show', $p->sale) }}" class="text-decoration-none small">{{ $p->sale->sale_no }}</a></td>
                        <td class="small">{{ $p->sale->member->name ?? 'ลูกค้าทั่วไป' }}</td>
                        <td class="text-center"><span class="badge bg-light text-dark"><i class="bi {{ $p->method_icon }} me-1"></i>{{ $p->method_name }}</span></td>
                        <td class="small text-muted">{{ $p->reference_no ?? '-' }}</td>
                        <td class="small text-muted">{{ $p->user->name ?? '-' }}</td>
                        <td class="small text-muted">{{ $p->paid_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-end text-money fw-semibold">{{ number_format($p->amount, 2) }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $p->status === 'success' ? 'success' : ($p->status === 'refunded' ? 'danger' : 'warning') }}">
                                {{ ['success' => 'สำเร็จ', 'refunded' => 'คืนเงิน', 'pending' => 'รอดำเนินการ'][$p->status] ?? $p->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-5">ไม่พบรายการรับชำระเงิน</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
        <div class="card-footer bg-white">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
