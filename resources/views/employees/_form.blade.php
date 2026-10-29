@php $e = $employee ?? null; @endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">รหัสพนักงาน</label>
        <input type="text" class="form-control" value="{{ $e->employee_code ?? $nextCode }}" disabled>
    </div>

    <div class="col-md-8">
        <label class="form-label required">ชื่อ-นามสกุล</label>
        <input type="text" name="name" value="{{ old('name', $e->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label required">อีเมล (ใช้เข้าสู่ระบบ)</label>
        <input type="email" name="email" value="{{ old('email', $e->email ?? '') }}"
               class="form-control @error('email') is-invalid @enderror" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">เบอร์โทรศัพท์</label>
        <input type="text" name="phone" value="{{ old('phone', $e->phone ?? '') }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label {{ $e ? '' : 'required' }}">รหัสผ่าน</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $e ? '' : 'required' }}>
        <div class="form-text">{{ $e ? 'เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน' : 'อย่างน้อย 8 ตัวอักษร' }}</div>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label {{ $e ? '' : 'required' }}">ยืนยันรหัสผ่าน</label>
        <input type="password" name="password_confirmation" class="form-control" {{ $e ? '' : 'required' }}>
    </div>

    <div class="col-md-4">
        <label class="form-label required">สิทธิ์การใช้งาน</label>
        <select name="role" class="form-select @error('role') is-invalid @enderror">
            <option value="cashier" @selected(old('role', $e->role ?? 'cashier') === 'cashier')>พนักงานขาย (ขาย/สมาชิก/ดูสินค้า)</option>
            <option value="manager" @selected(old('role', $e->role ?? '') === 'manager')>ผู้จัดการร้าน (เพิ่มรายงาน/พนักงาน)</option>
            <option value="admin" @selected(old('role', $e->role ?? '') === 'admin')>ผู้ดูแลระบบ (ทุกเมนู)</option>
        </select>
        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">ตำแหน่งงาน</label>
        <input type="text" name="position" value="{{ old('position', $e->position ?? '') }}" class="form-control" placeholder="เช่น แคชเชียร์">
    </div>

    <div class="col-md-4">
        <label class="form-label">เงินเดือน (บาท)</label>
        <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', $e->salary ?? 0) }}" class="form-control text-end">
    </div>

    <div class="col-md-4">
        <label class="form-label">วันที่เริ่มงาน</label>
        <input type="date" name="hired_at"
               value="{{ old('hired_at', optional($e?->hired_at)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
               class="form-control">
    </div>

    <div class="col-md-8">
        <label class="form-label">ที่อยู่</label>
        <textarea name="address" rows="2" class="form-control">{{ old('address', $e->address ?? '') }}</textarea>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1"
                   @checked(old('status', $e->status ?? true)) {{ $e && $e->id === auth()->id() ? 'disabled' : '' }}>
            <label class="form-check-label" for="status">เปิดใช้งานบัญชีพนักงาน</label>
        </div>
    </div>
</div>
