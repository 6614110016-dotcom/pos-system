<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * โมดูลยืนยันตัวตน (Login)
 * - เข้าสู่ระบบด้วยอีเมล + รหัสผ่าน
 * - จำกัดจำนวนครั้งที่กรอกผิด (Rate Limit) 5 ครั้ง / 60 วินาที
 * - ตรวจสอบสถานะบัญชีว่ายังใช้งานอยู่หรือไม่
 */
class LoginController extends Controller
{
    /** แสดงหน้าเข้าสู่ระบบ */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /** ตรวจสอบข้อมูลและเข้าสู่ระบบ */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'กรุณากรอกอีเมล',
            'email.email'       => 'รูปแบบอีเมลไม่ถูกต้อง',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
        ]);

        $throttleKey = strtolower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "พยายามเข้าสู่ระบบผิดหลายครั้งเกินไป กรุณาลองใหม่ใน {$seconds} วินาที",
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
            ]);
        }

        // บัญชีถูกระงับการใช้งาน
        if (! Auth::user()->status) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        Auth::user()->update(['last_login_at' => now()]);
        ActivityLog::record('login', 'Login', 'เข้าสู่ระบบ');

        return redirect()->intended(route('dashboard'))
            ->with('success', 'ยินดีต้อนรับ '.Auth::user()->name);
    }

    /** ออกจากระบบ */
    public function logout(Request $request): RedirectResponse
    {
        ActivityLog::record('logout', 'Login', 'ออกจากระบบ');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'ออกจากระบบเรียบร้อยแล้ว');
    }

    /** แสดงหน้าเปลี่ยนรหัสผ่านของตนเอง */
    public function showChangePassword(): View
    {
        return view('auth.change-password');
    }

    /** บันทึกรหัสผ่านใหม่ */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required'  => 'กรุณากรอกรหัสผ่านปัจจุบัน',
            'current_password.current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง',
            'password.required'          => 'กรุณากรอกรหัสผ่านใหม่',
            'password.min'               => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed'         => 'การยืนยันรหัสผ่านไม่ตรงกัน',
        ]);

        $request->user()->update(['password' => $request->password]);
        ActivityLog::record('update', 'Login', 'เปลี่ยนรหัสผ่านของตนเอง');

        return back()->with('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    }
}
