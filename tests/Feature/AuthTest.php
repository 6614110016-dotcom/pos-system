<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ทดสอบโมดูลยืนยันตัวตน (Login)
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_หน้าเข้าสู่ระบบเปิดได้(): void
    {
        $this->get('/login')->assertOk()->assertSee('เข้าสู่ระบบ');
    }

    public function test_เข้าสู่ระบบด้วยข้อมูลที่ถูกต้องได้(): void
    {
        $user = User::factory()->create(['email' => 'test@pos.test']);

        $this->post('/login', [
            'email'    => 'test@pos.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_เข้าสู่ระบบด้วยรหัสผ่านผิดไม่ได้(): void
    {
        User::factory()->create(['email' => 'test@pos.test']);

        $this->post('/login', [
            'email'    => 'test@pos.test',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_บัญชีที่ถูกระงับเข้าสู่ระบบไม่ได้(): void
    {
        User::factory()->create(['email' => 'blocked@pos.test', 'status' => false]);

        $this->post('/login', [
            'email'    => 'blocked@pos.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_ผู้ที่ยังไม่เข้าสู่ระบบถูกส่งไปหน้าล็อกอิน(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }
}
