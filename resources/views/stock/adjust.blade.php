@extends('layouts.app')
@section('title', 'ปรับยอดสต็อก')
@section('subtitle', $product->product_code . ' - ' . $product->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-sliders me-2"></i>ปรับยอดสต็อกให้ตรงกับของจริง</div>
            <form method="POST" action="{{ route('stock.adjust', $product) }}">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="alert alert-info small">
                        ใช้เมนูนี้เมื่อ <strong>นับสต็อกแล้วไม่ตรงกับระบบ</strong> เช่น สินค้าเสียหาย สูญหาย หรือคีย์ผิด
                        ระบบจะบันทึกส่วนต่างไว้ในประวัติการเคลื่อนไหวสต็อกทั้งหมด
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ยอดคงเหลือในระบบ</label>
                            <input type="text" class="form-control form-control-lg text-end" id="systemQty"
                                   value="{{ $product->stock_qty }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">จำนวนที่นับได้จริง</label>
                            <input type="number" min="0" name="actual_qty" id="actualQty"
                                   value="{{ old('actual_qty', $product->stock_qty) }}"
                                   class="form-control form-control-lg text-end @error('actual_qty') is-invalid @enderror"
                                   required oninput="calcDiff()">
                            @error('actual_qty')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <div class="alert alert-secondary d-flex justify-content-between mb-0">
                                <span class="fw-semibold">ส่วนต่างที่จะปรับ</span>
                                <span class="fs-5 fw-bold" id="diffText">0</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label required">เหตุผลในการปรับยอด</label>
                            <textarea name="note" rows="2" class="form-control @error('note') is-invalid @enderror"
                                      placeholder="เช่น สินค้าหมดอายุ 3 ชิ้น / นับสต็อกประจำเดือน" required>{{ old('note') }}</textarea>
                            @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button class="btn btn-warning"><i class="bi bi-check-lg me-1"></i>ยืนยันการปรับยอด</button>
                    <a href="{{ route('stock.index') }}" class="btn btn-light">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const systemQty = {{ $product->stock_qty }};

    function calcDiff() {
        const actual = parseInt(document.getElementById('actualQty').value || 0, 10);
        const diff   = actual - systemQty;
        const el     = document.getElementById('diffText');
        el.textContent = (diff > 0 ? '+' : '') + diff + ' {{ $product->unit }}';
        el.className   = 'fs-5 fw-bold ' + (diff > 0 ? 'text-success' : diff < 0 ? 'text-danger' : 'text-muted');
    }

    calcDiff();
</script>
@endpush
