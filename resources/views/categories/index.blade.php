@extends('layouts.app')
@section('title', 'หมวดหมู่สินค้า')
@section('subtitle', 'จัดกลุ่มสินค้าเพื่อให้ค้นหาบนหน้าจอขายได้เร็วขึ้น')

@section('content')
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="ค้นหาชื่อหมวดหมู่">
            </div>
            <div class="col-md-8 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-search me-1"></i>ค้นหา</button>
                <a href="{{ route('categories.index') }}" class="btn btn-light">ล้าง</a>
                <a href="{{ route('categories.create') }}" class="btn btn-success ms-auto"><i class="bi bi-plus-lg me-1"></i>เพิ่มหมวดหมู่</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>รหัส</th><th>ชื่อหมวดหมู่</th><th>คำอธิบาย</th>
                    <th class="text-center">จำนวนสินค้า</th><th class="text-center">สถานะ</th><th class="text-center">จัดการ</th></tr>
            </thead>
            <tbody>
                @forelse($categories as $c)
                    <tr>
                        <td class="fw-semibold">{{ $c->code }}</td>
                        <td class="fw-semibold">{{ $c->name }}</td>
                        <td class="small text-muted">{{ $c->description ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('products.index', ['category_id' => $c->id]) }}" class="badge bg-primary text-decoration-none">
                                {{ $c->products_count }} รายการ
                            </a>
                        </td>
                        <td class="text-center"><span class="badge bg-{{ $c->status ? 'success' : 'secondary' }}">{{ $c->status ? 'ใช้งาน' : 'ปิด' }}</span></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('categories.edit', $c) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('categories.destroy', $c) }}" data-confirm="ยืนยันการลบหมวดหมู่ {{ $c->name }} ?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">ยังไม่มีหมวดหมู่สินค้า</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="card-footer bg-white">{{ $categories->links() }}</div>
    @endif
</div>
@endsection
