@extends('layouts.app')
@section('title', 'บันทึกการใช้งานระบบ')
@section('subtitle', 'ตรวจสอบว่าใครทำอะไรกับระบบบ้าง (Audit Log)')

@section('content')
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <select name="action" class="form-select">
                    <option value="">ทุกการกระทำ</option>
                    @foreach(['login' => 'เข้าสู่ระบบ', 'logout' => 'ออกจากระบบ', 'create' => 'เพิ่มข้อมูล', 'update' => 'แก้ไขข้อมูล', 'delete' => 'ลบข้อมูล', 'sale' => 'เปิดบิลขาย', 'void' => 'ยกเลิกบิล'] as $key => $label)
                        <option value="{{ $key }}" @selected(request('action') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="module" class="form-select">
                    <option value="">ทุกโมดูล</option>
                    @foreach(['Login', 'Member', 'Employee', 'Product', 'Category', 'Stock', 'Sale', 'Payment', 'Setting'] as $mod)
                        <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-search me-1"></i>ค้นหา</button>
                <a href="{{ route('settings.logs') }}" class="btn btn-light">ล้าง</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>วันที่-เวลา</th><th>ผู้ใช้งาน</th><th class="text-center">การกระทำ</th>
                    <th class="text-center">โมดูล</th><th>รายละเอียด</th><th>IP</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="small">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="small">{{ $log->user->name ?? 'ระบบ' }}</td>
                        <td class="text-center"><span class="badge bg-{{ $log->action_badge }}">{{ $log->action }}</span></td>
                        <td class="text-center small text-muted">{{ $log->module }}</td>
                        <td class="small">{{ $log->description }}</td>
                        <td class="small text-muted">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">ยังไม่มีบันทึกการใช้งาน</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="card-footer bg-white">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
