<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * โมดูลสมาชิก (Member)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code', 20)->unique()->comment('รหัสสมาชิก');
            $table->string('name', 150)->comment('ชื่อ-นามสกุล');
            $table->string('phone', 20)->unique()->comment('เบอร์โทรศัพท์ (ใช้ค้นหาที่จุดขาย)');
            $table->string('email')->nullable()->comment('อีเมล');
            $table->text('address')->nullable()->comment('ที่อยู่');
            $table->date('birth_date')->nullable()->comment('วันเกิด');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->comment('เพศ');
            $table->enum('tier', ['silver', 'gold', 'platinum'])
                  ->default('silver')
                  ->comment('ระดับสมาชิก (ใช้กำหนดส่วนลด)');
            $table->integer('points')->default(0)->comment('แต้มสะสม');
            $table->decimal('total_spent', 12, 2)->default(0)->comment('ยอดซื้อสะสม');
            $table->date('joined_at')->nullable()->comment('วันที่สมัครสมาชิก');
            $table->boolean('status')->default(true)->comment('สถานะสมาชิก');
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
