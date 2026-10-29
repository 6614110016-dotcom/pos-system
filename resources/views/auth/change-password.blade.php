@extends('layouts.app')
@section('title', 'เปลี่ยนรหัสผ่าน')
@section('subtitle', 'เปลี่ยนรหัสผ่านสำหรับเข้าใช้งานระบบของคุณ')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-key me-2"></i>เปลี่ยนรหัสผ่าน</div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label required">รหัสผ่านปัจจุบัน</label>
                        <input type="password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">รหัสผ่านใหม่</label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        <div class="form-text">อย่างน้อย 8 ตัวอักษร</div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label required">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกรหัสผ่านใหม่</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-light">ยกเลิก</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
