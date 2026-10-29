@extends('layouts.app')
@section('title', 'เพิ่มพนักงานใหม่')
@section('subtitle', 'สร้างบัญชีผู้ใช้งานและกำหนดสิทธิ์ให้พนักงาน')

@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-person-badge me-2"></i>ข้อมูลพนักงาน</div>
    <form method="POST" action="{{ route('employees.store') }}">
        @csrf
        <div class="card-body">@include('employees._form', ['employee' => null])</div>
        <div class="card-footer bg-white d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกข้อมูล</button>
            <a href="{{ route('employees.index') }}" class="btn btn-light">ยกเลิก</a>
        </div>
    </form>
</div>
@endsection
