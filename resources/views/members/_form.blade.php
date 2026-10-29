@php $m = $member ?? null; @endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">รหัสสมาชิก</label>
        <input type="text" class="form-control" value="{{ $m->member_code ?? $nextCode }}" disabled>
        <div class="form-text">ระบบสร้างรหัสให้อัตโนมัติ</div>
    </div>

    <div class="col-md-8">
        <label class="form-label required">ชื่อ-นามสกุล</label>
        <input type="text" name="name" value="{{ old('name', $m->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label required">เบอร์โทรศัพท์</label>
        <input type="text" name="phone" value="{{ old('phone', $m->phone ?? '') }}"
               class="form-control @error('phone') is-invalid @enderror" placeholder="08xxxxxxxx" required>
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">อีเมล</label>
        <input type="email" name="email" value="{{ old('email', $m->email ?? '') }}"
               class="form-control @error('email') is-invalid @enderror">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">วันเกิด</label>
        <input type="date" name="birth_date" value="{{ old('birth_date', optional($m?->birth_date)->format('Y-m-d')) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">เพศ</label>
        <select name="gender" class="form-select">
            <option value="">- ไม่ระบุ -</option>
            <option value="male" @selected(old('gender', $m->gender ?? '') === 'male')>ชาย</option>
            <option value="female" @selected(old('gender', $m->gender ?? '') === 'female')>หญิง</option>
            <option value="other" @selected(old('gender', $m->gender ?? '') === 'other')>อื่นๆ</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label required">ระดับสมาชิก</label>
        <select name="tier" class="form-select @error('tier') is-invalid @enderror">
            <option value="silver" @selected(old('tier', $m->tier ?? 'silver') === 'silver')>เงิน (Silver) - ไม่มีส่วนลด</option>
            <option value="gold" @selected(old('tier', $m->tier ?? '') === 'gold')>ทอง (Gold) - ลด 3%</option>
            <option value="platinum" @selected(old('tier', $m->tier ?? '') === 'platinum')>แพลทินัม (Platinum) - ลด 5%</option>
        </select>
        <div class="form-text">ระบบจะเลื่อนระดับอัตโนมัติตามยอดซื้อสะสม</div>
    </div>

    <div class="col-md-4">
        <label class="form-label">แต้มสะสม</label>
        <input type="number" name="points" value="{{ old('points', $m->points ?? 0) }}" min="0" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">วันที่สมัครสมาชิก</label>
        <input type="date" name="joined_at"
               value="{{ old('joined_at', optional($m?->joined_at)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
               class="form-control">
    </div>

    <div class="col-md-8">
        <label class="form-label">ที่อยู่</label>
        <textarea name="address" rows="2" class="form-control">{{ old('address', $m->address ?? '') }}</textarea>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1"
                   @checked(old('status', $m->status ?? true))>
            <label class="form-check-label" for="status">เปิดใช้งานบัญชีสมาชิก</label>
        </div>
    </div>
</div>
