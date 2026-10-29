@extends('layouts.app')
@section('title', 'แก้ไขข้อมูลสินค้า')
@section('subtitle', $product->product_code . ' - ' . $product->name)

@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลสินค้า</div>
    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="card-body">@include('products._form', ['product' => $product, 'nextCode' => $product->product_code])</div>
        <div class="card-footer bg-white d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกการแก้ไข</button>
            <a href="{{ route('products.index') }}" class="btn btn-light">ยกเลิก</a>
        </div>
    </form>
</div>
@endsection
