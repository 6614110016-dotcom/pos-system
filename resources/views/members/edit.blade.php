@extends('layouts.app')
@section('title', 'แก้ไขข้อมูลสมาชิก')
@section('subtitle', $member->member_code . ' - ' . $member->name)

@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลสมาชิก</div>
    <form method="POST" action="{{ route('members.update', $member) }}">
        @csrf @method('PUT')
        <div class="card-body">
            @include('members._form', ['member' => $member, 'nextCode' => $member->member_code])
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกการแก้ไข</button>
            <a href="{{ route('members.index') }}" class="btn btn-light">ยกเลิก</a>
        </div>
    </form>
</div>
@endsection
