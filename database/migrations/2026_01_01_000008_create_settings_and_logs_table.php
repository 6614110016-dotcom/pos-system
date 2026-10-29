<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * โมดูลอื่นๆ เพิ่มเติม
 * - settings      : ตั้งค่าระบบ (ชื่อร้าน, VAT, แต้มสะสม, ส่วนลดสมาชิก)
 * - activity_logs : บันทึกการใช้งานระบบของผู้ใช้ (Audit Log)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50)->comment('login, logout, create, update, delete, sale, void');
            $table->string('module', 50)->comment('โมดูลที่ทำรายการ');
            $table->string('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
    }
};
