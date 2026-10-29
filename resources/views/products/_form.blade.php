@php $p = $product ?? null; @endphp

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">รหัสสินค้า</label>
        <input type="text" class="form-control" value="{{ $p->product_code ?? $nextCode }}" disabled>
    </div>

    <div class="col-md-6">
        <label class="form-label required">ชื่อสินค้า</label>
        <input type="text" name="name" value="{{ old('name', $p->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">บาร์โค้ด</label>
        <input type="text" name="barcode" value="{{ old('barcode', $p->barcode ?? '') }}"
               class="form-control @error('barcode') is-invalid @enderror" placeholder="สแกนหรือพิมพ์">
        @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label required">หมวดหมู่</label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">- เลือกหมวดหมู่ -</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(old('category_id', $p->category_id ?? '') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-2">
        <label class="form-label required">หน่วยนับ</label>
        <input type="text" name="unit" value="{{ old('unit', $p->unit ?? 'ชิ้น') }}" class="form-control" required>
    </div>

    <div class="col-md-3">
        <label class="form-label required">ราคาทุน (บาท)</label>
        <input type="number" step="0.01" min="0" name="cost_price" value="{{ old('cost_price', $p->cost_price ?? 0) }}"
               class="form-control text-end @error('cost_price') is-invalid @enderror" required>
        @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label required">ราคาขาย (บาท)</label>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $p->price ?? 0) }}"
               class="form-control text-end @error('price') is-invalid @enderror" required>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if(! $p)
        <div class="col-md-3">
            <label class="form-label">จำนวนสต็อกยกมา</label>
            <input type="number" min="0" name="stock_qty" value="{{ old('stock_qty', 0) }}" class="form-control text-end">
            <div class="form-text">บันทึกเป็นรายการรับเข้าครั้งแรก</div>
        </div>
    @else
        <div class="col-md-3">
            <label class="form-label">จำนวนคงเหลือปัจจุบัน</label>
            <input type="text" class="form-control text-end" value="{{ number_format($p->stock_qty) }} {{ $p->unit }}" disabled>
            <div class="form-text">แก้ไขผ่านเมนู <a href="{{ route('stock.adjust.form', $p) }}">ปรับยอดสต็อก</a></div>
        </div>
    @endif

    <div class="col-md-3">
        <label class="form-label required">จุดสั่งซื้อ (Reorder)</label>
        <input type="number" min="0" name="reorder_level" value="{{ old('reorder_level', $p->reorder_level ?? 10) }}"
               class="form-control text-end" required>
        <div class="form-text">แจ้งเตือนเมื่อสต็อกเหลือน้อยกว่านี้</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">รูปสินค้า</label>
        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if($p && $p->image)
            <div class="mt-2"><img src="{{ $p->image_url }}" style="height:70px;border-radius:.4rem" alt=""></div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label">รายละเอียดสินค้า</label>
        <textarea name="description" rows="3" class="form-control">{{ old('description', $p->description ?? '') }}</textarea>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $p->status ?? true))>
            <label class="form-check-label" for="status">เปิดขายสินค้านี้ (แสดงบนหน้าจอ POS)</label>
        </div>
    </div>
</div>
