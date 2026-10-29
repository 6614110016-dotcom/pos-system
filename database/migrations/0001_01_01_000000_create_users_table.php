<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * โมดูลพนักงาน (Employee) + โมดูลยืนยันตัวตน (Login)
 * ตาราง users เก็บข้อมูลพนักงานทั้งหมดและใช้เป็นบัญชีเข้าสู่ระบบ
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 20)->unique()->comment('รหัสพนักงาน');
            $table->string('name')->comment('ชื่อ-นามสกุล');
            $table->string('email')->unique()->comment('อีเมล (ใช้เข้าสู่ระบบ)');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable()->comment('เบอร์โทรศัพท์');
            $table->text('address')->nullable()->comment('ที่อยู่');
            $table->enum('role', ['admin', 'manager', 'cashier'])
                  ->default('cashier')
                  ->comment('สิทธิ์: ผู้ดูแลระบบ / ผู้จัดการ / พนักงานขาย');
            $table->string('position', 100)->nullable()->comment('ตำแหน่งงาน');
            $table->decimal('salary', 10, 2)->default(0)->comment('เงินเดือน');
            $table->date('hired_at')->nullable()->comment('วันที่เริ่มงาน');
            $table->boolean('status')->default(true)->comment('1 = ใช้งาน, 0 = ระงับการใช้งาน');
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
