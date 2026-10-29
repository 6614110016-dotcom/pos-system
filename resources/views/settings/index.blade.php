@extends('layouts.app')
@section('title', 'ตั้งค่าระบบ')
@section('subtitle', 'ข้อมูลร้าน อัตราภาษี และเงื่อนไขการสะสมแต้ม')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf @method('PUT')

            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-shop me-2"></i>ข้อมูลร้านค้า (แสดงบนใบเสร็จ)</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label required">ชื่อร้าน</label>
                            <input type="text" name="shop_name" value="{{ old('shop_name', $settings['shop_name']) }}"
                                   class="form-control @error('shop_name') is-invalid @enderror" required>
                            @error('shop_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" name="shop_phone" value="{{ old('shop_phone', $settings['shop_phone']) }}" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">ที่อยู่ร้าน</label>
                            <textarea name="shop_address" rows="2" class="form-control">{{ old('shop_address', $settings['shop_address']) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">เลขประจำตัวผู้เสียภาษี</label>
                            <input type="text" name="shop_tax_id" value="{{ old('shop_tax_id', $settings['shop_tax_id']) }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-calculator me-2"></i>การคำนวณเงิน</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">อัตราภาษีมูลค่าเพิ่ม (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="vat_rate"
                                   value="{{ old('vat_rate', $settings['vat_rate']) }}"
                                   class="form-control @error('vat_rate') is-invalid @enderror" required>
                            <div class="form-text">ระบบคิดแบบ VAT รวมในราคาสินค้าแล้ว (Included VAT)</div>
                            @error('vat_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">ซื้อครบกี่บาทได้ 1 แต้ม</label>
                            <input type="number" step="1" min="1" name="point_rate"
                                   value="{{ old('point_rate', $settings['point_rate']) }}"
                                   class="form-control @error('point_rate') is-invalid @enderror" required>
                            <div class="form-text">เช่น 100 = ซื้อครบ 100 บาท ได้ 1 แต้ม</div>
                            @error('point_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-receipt me-2"></i>ใบเสร็จ</div>
                <div class="card-body">
                    <label class="form-label">ข้อความท้ายใบเสร็จ</label>
                    <input type="text" name="receipt_note" value="{{ old('receipt_note', $settings['receipt_note']) }}"
                           class="form-control" placeholder="เช่น ขอบคุณที่ใช้บริการ">
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        ส่วนลดตามระดับสมาชิกกำหนดไว้ในโค้ด (Silver 0% / Gold 3% / Platinum 5%)
                        และระบบจะเลื่อนระดับให้อัตโนมัติเมื่อยอดซื้อสะสมถึง 5,000 และ 20,000 บาทตามลำดับ
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกการตั้งค่า</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
