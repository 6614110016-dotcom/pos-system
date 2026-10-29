<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * โมดูลสมาชิก (Member)
 * เพิ่ม / แก้ไข / ลบ / ค้นหา สมาชิก และดูประวัติการซื้อ
 */
class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $members = Member::search($request->q)
            ->when($request->tier, fn ($q, $tier) => $q->where('tier', $tier))
            ->when($request->filled('status'), fn ($q) => $q->where('status', (bool) $request->status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('members.index', compact('members'));
    }

    public function create(): View
    {
        return view('members.create', [
            'nextCode' => Member::generateCode(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['member_code'] = Member::generateCode();
        $data['joined_at']   = $data['joined_at'] ?? now()->toDateString();
        $data['status']      = $request->boolean('status', true);

        $member = Member::create($data);
        ActivityLog::record('create', 'Member', "เพิ่มสมาชิก {$member->member_code} - {$member->name}");

        return redirect()->route('members.index')
            ->with('success', "เพิ่มสมาชิก \"{$member->name}\" เรียบร้อยแล้ว (รหัส {$member->member_code})");
    }

    public function show(Member $member): View
    {
        $member->load(['sales' => fn ($q) => $q->latest('id')->limit(20)]);

        $summary = [
            'bills'     => $member->sales()->where('status', 'paid')->count(),
            'total'     => (float) $member->sales()->where('status', 'paid')->sum('total'),
            'last_buy'  => $member->sales()->where('status', 'paid')->max('sold_at'),
        ];

        return view('members.show', compact('member', 'summary'));
    }

    public function edit(Member $member): View
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $data = $this->validateData($request, $member->id);
        $data['status'] = $request->boolean('status');

        $member->update($data);
        ActivityLog::record('update', 'Member', "แก้ไขสมาชิก {$member->member_code}");

        return redirect()->route('members.index')
            ->with('success', "แก้ไขข้อมูลสมาชิก \"{$member->name}\" เรียบร้อยแล้ว");
    }

    public function destroy(Member $member): RedirectResponse
    {
        if ($member->sales()->exists()) {
            return back()->with('error', 'ไม่สามารถลบสมาชิกที่มีประวัติการซื้อได้ กรุณาเปลี่ยนสถานะเป็น "ระงับ" แทน');
        }

        $name = $member->name;
        $member->delete();
        ActivityLog::record('delete', 'Member', "ลบสมาชิก {$name}");

        return redirect()->route('members.index')->with('success', "ลบสมาชิก \"{$name}\" เรียบร้อยแล้ว");
    }

    /** ค้นหาสมาชิกจากหน้าจอ POS (AJAX) */
    public function search(Request $request)
    {
        $members = Member::active()
            ->search($request->q)
            ->limit(10)
            ->get(['id', 'member_code', 'name', 'phone', 'tier', 'points']);

        return response()->json(
            $members->map(fn ($m) => [
                'id'          => $m->id,
                'member_code' => $m->member_code,
                'name'        => $m->name,
                'phone'       => $m->phone,
                'tier'        => $m->tier_name,
                'discount'    => $m->discount_rate,
                'points'      => $m->points,
            ])
        );
    }

    /** กติกาการตรวจสอบข้อมูล */
    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:150'],
            'phone'      => ['required', 'string', 'max:20', Rule::unique('members', 'phone')->ignore($ignoreId)],
            'email'      => ['nullable', 'email', 'max:255'],
            'address'    => ['nullable', 'string', 'max:500'],
            'birth_date' => ['nullable', 'date'],
            'gender'     => ['nullable', Rule::in(['male', 'female', 'other'])],
            'tier'       => ['required', Rule::in(['silver', 'gold', 'platinum'])],
            'points'     => ['nullable', 'integer', 'min:0'],
            'joined_at'  => ['nullable', 'date'],
        ], [
            'name.required'  => 'กรุณากรอกชื่อ-นามสกุลสมาชิก',
            'phone.required' => 'กรุณากรอกเบอร์โทรศัพท์',
            'phone.unique'   => 'เบอร์โทรศัพท์นี้ถูกใช้สมัครสมาชิกแล้ว',
            'email.email'    => 'รูปแบบอีเมลไม่ถูกต้อง',
            'tier.required'  => 'กรุณาเลือกระดับสมาชิก',
        ]);
    }
}
