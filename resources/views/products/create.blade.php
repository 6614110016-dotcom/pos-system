@extends('layouts.app')
@section('title', 'เพิ่มสินค้าใหม่')
@section('subtitle', 'กรอกข้อมูลสินค้าและจำนวนสต็อกยกมา')

@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-box-seam me-2"></i>ข้อมูลสินค้า</div>
    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">@include('products._form', ['product' => null])</div>
        <div class="card-footer bg-white d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกสินค้า</button>
            <a href="{{ route('products.index') }}" class="btn btn-light">ยกเลิก</a>
        </div>
    </form>
</div>
@endsection
