@extends('layouts.app')
@section('title', 'เพิ่มสมาชิกใหม่')
@section('subtitle', 'กรอกข้อมูลสมาชิกเพื่อรับส่วนลดและสะสมแต้ม')

@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-person-plus me-2"></i>ข้อมูลสมาชิก</div>
    <form method="POST" action="{{ route('members.store') }}">
        @csrf
        <div class="card-body">
            @include('members._form', ['member' => null])
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกข้อมูล</button>
            <a href="{{ route('members.index') }}" class="btn btn-light">ยกเลิก</a>
        </div>
    </form>
</div>
@endsection
