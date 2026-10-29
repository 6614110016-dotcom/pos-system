<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * โมดูลอื่นๆ - ตั้งค่าระบบ
 */
class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'shop_name'    => Setting::get('shop_name', config('app.shop.name')),
            'shop_address' => Setting::get('shop_address', config('app.shop.address')),
            'shop_phone'   => Setting::get('shop_phone', config('app.shop.phone')),
            'shop_tax_id'  => Setting::get('shop_tax_id', config('app.shop.tax_id')),
            'vat_rate'     => Setting::vatRate(),
            'point_rate'   => Setting::pointRate(),
            'receipt_note' => Setting::get('receipt_note', 'ขอบคุณที่ใช้บริการ'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shop_name'    => ['required', 'string', 'max:150'],
            'shop_address' => ['nullable', 'string', 'max:500'],
            'shop_phone'   => ['nullable', 'string', 'max:50'],
            'shop_tax_id'  => ['nullable', 'string', 'max:20'],
            'vat_rate'     => ['required', 'numeric', 'min:0', 'max:100'],
            'point_rate'   => ['required', 'numeric', 'min:1'],
            'receipt_note' => ['nullable', 'string', 'max:255'],
        ], [
            'shop_name.required'  => 'กรุณากรอกชื่อร้าน',
            'vat_rate.required'   => 'กรุณากรอกอัตราภาษีมูลค่าเพิ่ม',
            'point_rate.required' => 'กรุณากรอกอัตราการสะสมแต้ม',
            'point_rate.min'      => 'อัตราการสะสมแต้มต้องมากกว่า 0',
        ]);

        foreach ($data as $key => $value) {
            Setting::put($key, $value, 'general');
        }

        ActivityLog::record('update', 'Setting', 'แก้ไขการตั้งค่าระบบ');

        return back()->with('success', 'บันทึกการตั้งค่าระบบเรียบร้อยแล้ว');
    }

    /** บันทึกการใช้งานระบบ (Audit Log) */
    public function logs(Request $request): View
    {
        $logs = ActivityLog::with('user')
            ->when($request->action, fn ($q, $a) => $q->where('action', $a))
            ->when($request->module, fn ($q, $m) => $q->where('module', $m))
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('settings.logs', compact('logs'));
    }
}
