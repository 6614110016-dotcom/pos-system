@extends('layouts.app')
@section('title', 'แก้ไขหมวดหมู่สินค้า')
@section('subtitle', $category->code . ' - ' . $category->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil-square me-2"></i>แก้ไขหมวดหมู่</div>
            <form method="POST" action="{{ route('categories.update', $category) }}">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">รหัสหมวดหมู่</label>
                        <input type="text" class="form-control" value="{{ $category->code }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">ชื่อหมวดหมู่</label>
                        <input type="text" name="name" value="{{ old('name', $category->name) }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">คำอธิบาย</label>
                        <textarea name="description" rows="2" class="form-control">{{ old('description', $category->description) }}</textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input type="hidden" name="status" value="0">
                        <input type="checkbox" class="form-check-input" id="status" name="status" value="1"
                               @checked(old('status', $category->status))>
                        <label class="form-check-label" for="status">เปิดใช้งานหมวดหมู่นี้</label>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกการแก้ไข</button>
                    <a href="{{ route('categories.index') }}" class="btn btn-light">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
