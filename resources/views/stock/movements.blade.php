@extends('layouts.app')
@section('title', 'ประวัติการเคลื่อนไหวสต็อก')
@section('subtitle', 'ตรวจสอบการรับเข้า ตัดออก ปรับยอด และรับคืนสินค้าทั้งหมด')

@section('content')
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <select name="product_id" class="form-select">
                    <option value="">ทุกสินค้า</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>{{ $p->product_code }} - {{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">ทุกประเภท</option>
                    @foreach(\App\Models\StockMovement::TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
            <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>ค้นหา</button>
                <a href="{{ route('stock.movements') }}" class="btn btn-light">ล้าง</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>วันที่-เวลา</th><th>สินค้า</th><th class="text-center">ประเภท</th>
                    <th class="text-center">จำนวน</th><th class="text-center">ก่อน</th><th class="text-center">หลัง</th>
                    <th>อ้างอิง</th><th>หมายเหตุ</th><th>ผู้ทำรายการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $mv)
                    <tr>
                        <td class="small">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="small fw-semibold">{{ $mv->product->name ?? '-' }}</div>
                            <div class="text-muted" style="font-size:.72rem">{{ $mv->product->product_code ?? '' }}</div>
                        </td>
                        <td class="text-center"><span class="badge bg-{{ $mv->type_badge }}">{{ $mv->type_name }}</span></td>
                        <td class="text-center fw-semibold {{ $mv->qty > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $mv->qty > 0 ? '+' : '' }}{{ $mv->qty }}
                        </td>
                        <td class="text-center text-muted small">{{ $mv->qty_before }}</td>
                        <td class="text-center small">{{ $mv->qty_after }}</td>
                        <td class="small text-muted">{{ $mv->reference ?? '-' }}</td>
                        <td class="small text-muted">{{ $mv->note ?? '-' }}</td>
                        <td class="small text-muted">{{ $mv->user->name ?? 'ระบบ' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-5">ยังไม่มีประวัติการเคลื่อนไหวสต็อก</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($movements->hasPages())
        <div class="card-footer bg-white">{{ $movements->links() }}</div>
    @endif
</div>
@endsection
