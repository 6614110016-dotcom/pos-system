@extends('layouts.app')
@section('title', 'แก้ไขข้อมูลพนักงาน')
@section('subtitle', $employee->employee_code . ' - ' . $employee->name)

@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลพนักงาน</div>
    <form method="POST" action="{{ route('employees.update', $employee) }}">
        @csrf @method('PUT')
        <div class="card-body">@include('employees._form', ['employee' => $employee, 'nextCode' => $employee->employee_code])</div>
        <div class="card-footer bg-white d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกการแก้ไข</button>
            <a href="{{ route('employees.index') }}" class="btn btn-light">ยกเลิก</a>
        </div>
    </form>
</div>
@endsection
