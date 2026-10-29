@extends('layouts.app')
@section('title', 'โมดูลสมาชิก')
@section('subtitle', 'จัดการข้อมูลสมาชิกของร้าน แต้มสะสม และระดับส่วนลด')

@section('content')
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                       placeholder="ค้นหาชื่อ / เบอร์โทร / รหัสสมาชิก">
            </div>
            <div class="col-md-2">
                <select name="tier" class="form-select">
                    <option value="">ทุกระดับ</option>
                    <option value="silver" @selected(request('tier') === 'silver')>เงิน (Silver)</option>
                    <option value="gold" @selected(request('tier') === 'gold')>ทอง (Gold)</option>
                    <option value="platinum" @selected(request('tier') === 'platinum')>แพลทินัม</option>
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
                <a href="{{ route('members.index') }}" class="btn btn-light">ล้าง</a>
                <a href="{{ route('members.create') }}" class="btn btn-success ms-auto">
                    <i class="bi bi-person-plus me-1"></i>เพิ่มสมาชิก
                </a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>รหัส</th><th>ชื่อ-นามสกุล</th><th>เบอร์โทร</th>
                    <th class="text-center">ระดับ</th>
                    <th class="text-end">ยอดซื้อสะสม</th>
                    <th class="text-center">แต้ม</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    <tr>
                        <td class="fw-semibold">{{ $member->member_code }}</td>
                        <td>
                            <a href="{{ route('members.show', $member) }}" class="text-decoration-none fw-semibold">{{ $member->name }}</a>
                            @if($member->email)<div class="text-muted" style="font-size:.75rem">{{ $member->email }}</div>@endif
                        </td>
                        <td>{{ $member->phone }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $member->tier_badge }}">{{ $member->tier_name }}</span>
                            <div class="text-muted" style="font-size:.72rem">ลด {{ $member->discount_rate }}%</div>
                        </td>
                        <td class="text-end text-money">{{ number_format($member->total_spent, 2) }}</td>
                        <td class="text-center">{{ number_format($member->points) }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $member->status ? 'success' : 'secondary' }}">
                                {{ $member->status ? 'ใช้งาน' : 'ระงับ' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('members.show', $member) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('members.edit', $member) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('members.destroy', $member) }}"
                                      data-confirm="ยืนยันการลบสมาชิก {{ $member->name }} ?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">ยังไม่มีข้อมูลสมาชิก</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($members->hasPages())
        <div class="card-footer bg-white">{{ $members->links() }}</div>
    @endif
</div>
@endsection
