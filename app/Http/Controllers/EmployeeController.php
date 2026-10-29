<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * โมดูลพนักงาน (Employee)
 * จัดการบัญชีพนักงานและกำหนดสิทธิ์ (admin / manager / cashier)
 * เข้าถึงได้เฉพาะผู้ดูแลระบบและผู้จัดการ
 */
class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $employees = User::query()
            ->when($request->q, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('employee_code', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->when($request->filled('status'), fn ($q) => $q->where('status', (bool) $request->status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', compact('employees'));
    }

    public function create(): View
    {
        return view('employees.create', [
            'nextCode' => User::generateCode(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'address'  => ['nullable', 'string', 'max:500'],
            'role'     => ['required', Rule::in(['admin', 'manager', 'cashier'])],
            'position' => ['nullable', 'string', 'max:100'],
            'salary'   => ['nullable', 'numeric', 'min:0'],
            'hired_at' => ['nullable', 'date'],
        ], $this->messages());

        $data['employee_code'] = User::generateCode();
        $data['status']        = $request->boolean('status', true);

        $employee = User::create($data);
        ActivityLog::record('create', 'Employee', "เพิ่มพนักงาน {$employee->employee_code} - {$employee->name}");

        return redirect()->route('employees.index')
            ->with('success', "เพิ่มพนักงาน \"{$employee->name}\" เรียบร้อยแล้ว (รหัส {$employee->employee_code})");
    }

    public function show(User $employee): View
    {
        $summary = [
            'bills'   => $employee->sales()->where('status', 'paid')->count(),
            'total'   => (float) $employee->sales()->where('status', 'paid')->sum('total'),
            'today'   => (float) $employee->sales()->where('status', 'paid')->whereDate('sold_at', today())->sum('total'),
        ];

        $recentSales = $employee->sales()->with('member')->latest('id')->limit(15)->get();

        return view('employees.show', compact('employee', 'summary', 'recentSales'));
    }

    public function edit(User $employee): View
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'address'  => ['nullable', 'string', 'max:500'],
            'role'     => ['required', Rule::in(['admin', 'manager', 'cashier'])],
            'position' => ['nullable', 'string', 'max:100'],
            'salary'   => ['nullable', 'numeric', 'min:0'],
            'hired_at' => ['nullable', 'date'],
        ], $this->messages());

        // ป้องกันการลดสิทธิ์ตนเองจนไม่มีผู้ดูแลระบบเหลืออยู่
        if ($employee->id === auth()->id() && $data['role'] !== 'admin' && $employee->isAdmin()) {
            return back()->with('error', 'ไม่สามารถเปลี่ยนสิทธิ์ของบัญชีตนเองได้');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $data['status'] = $employee->id === auth()->id() ? true : $request->boolean('status');

        $employee->update($data);
        ActivityLog::record('update', 'Employee', "แก้ไขข้อมูลพนักงาน {$employee->employee_code}");

        return redirect()->route('employees.index')
            ->with('success', "แก้ไขข้อมูลพนักงาน \"{$employee->name}\" เรียบร้อยแล้ว");
    }

    public function destroy(User $employee): RedirectResponse
    {
        if ($employee->id === auth()->id()) {
            return back()->with('error', 'ไม่สามารถลบบัญชีของตนเองได้');
        }

        if ($employee->sales()->exists()) {
            return back()->with('error', 'ไม่สามารถลบพนักงานที่มีประวัติการขายได้ กรุณาเปลี่ยนสถานะเป็น "ระงับ" แทน');
        }

        $name = $employee->name;
        $employee->delete();
        ActivityLog::record('delete', 'Employee', "ลบพนักงาน {$name}");

        return redirect()->route('employees.index')->with('success', "ลบพนักงาน \"{$name}\" เรียบร้อยแล้ว");
    }

    /** สลับสถานะใช้งาน/ระงับอย่างรวดเร็ว */
    public function toggleStatus(User $employee): RedirectResponse
    {
        if ($employee->id === auth()->id()) {
            return back()->with('error', 'ไม่สามารถระงับบัญชีของตนเองได้');
        }

        $employee->update(['status' => ! $employee->status]);

        return back()->with('success', $employee->status
            ? "เปิดใช้งานบัญชี \"{$employee->name}\" แล้ว"
            : "ระงับการใช้งานบัญชี \"{$employee->name}\" แล้ว");
    }

    protected function messages(): array
    {
        return [
            'name.required'      => 'กรุณากรอกชื่อ-นามสกุล',
            'email.required'     => 'กรุณากรอกอีเมล',
            'email.unique'       => 'อีเมลนี้ถูกใช้งานแล้ว',
            'password.required'  => 'กรุณากรอกรหัสผ่าน',
            'password.min'       => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'การยืนยันรหัสผ่านไม่ตรงกัน',
            'role.required'      => 'กรุณาเลือกสิทธิ์การใช้งาน',
        ];
    }
}
