@extends('layouts.app')
@section('title', 'โมดูลพนักงาน')
@section('subtitle', 'จัดการบัญชีพนักงานและกำหนดสิทธิ์การเข้าใช้งานระบบ')

@section('content')
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="ค้นหาชื่อ / รหัสพนักงาน / อีเมล">
            </div>
            <div class="col-md-2">
                <select name="role" class="form-select">
                    <option value="">ทุกสิทธิ์</option>
                    <option value="admin" @selected(request('role') === 'admin')>ผู้ดูแลระบบ</option>
                    <option value="manager" @selected(request('role') === 'manager')>ผู้จัดการร้าน</option>
                    <option value="cashier" @selected(request('role') === 'cashier')>พนักงานขาย</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">ทุกสถานะ</option>
                    <option value="1" @selected(request('status') === '1')>ใช้งาน</option>
                    <option value="0" @selected(request('status') === '0')>ระงับ</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-search me-1"></i>ค้นหา</button>
                <a href="{{ route('employees.index') }}" class="btn btn-light">ล้าง</a>
                <a href="{{ route('employees.create') }}" class="btn btn-success ms-auto"><i class="bi bi-person-plus me-1"></i>เพิ่มพนักงาน</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>รหัส</th><th>ชื่อ-นามสกุล</th><th>อีเมล</th><th>ตำแหน่ง</th>
                    <th class="text-center">สิทธิ์</th><th class="text-center">เข้าใช้ล่าสุด</th>
                    <th class="text-center">สถานะ</th><th class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr>
                        <td class="fw-semibold">{{ $emp->employee_code }}</td>
                        <td>
                            <a href="{{ route('employees.show', $emp) }}" class="text-decoration-none fw-semibold">{{ $emp->name }}</a>
                            @if($emp->id === auth()->id())<span class="badge bg-info ms-1">คุณ</span>@endif
                        </td>
                        <td class="small text-muted">{{ $emp->email }}</td>
                        <td class="small">{{ $emp->position ?? '-' }}</td>
                        <td class="text-center"><span class="badge bg-{{ $emp->role_badge }}">{{ $emp->role_name }}</span></td>
                        <td class="text-center small text-muted">{{ $emp->last_login_at?->format('d/m/Y H:i') ?? 'ยังไม่เคย' }}</td>
                        <td class="text-center">
                            <form method="POST" action="{{ route('employees.toggle', $emp) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm badge border-0 bg-{{ $emp->status ? 'success' : 'secondary' }} text-white">
                                    {{ $emp->status ? 'ใช้งาน' : 'ระงับ' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('employees.show', $emp) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('employees.edit', $emp) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                @if($emp->id !== auth()->id())
                                    <form method="POST" action="{{ route('employees.destroy', $emp) }}"
                                          data-confirm="ยืนยันการลบพนักงาน {{ $emp->name }} ?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">ไม่พบข้อมูลพนักงาน</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($employees->hasPages())
        <div class="card-footer bg-white">{{ $employees->links() }}</div>
    @endif
</div>
@endsection
