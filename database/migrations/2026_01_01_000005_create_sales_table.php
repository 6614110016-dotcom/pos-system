<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * โมดูลการขาย (Sale) - ตารางหัวบิลการขาย
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_no', 30)->unique()->comment('เลขที่บิล เช่น INV-20260904-0001');
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete()
                  ->comment('สมาชิกผู้ซื้อ (null = ลูกค้าทั่วไป)');
            $table->foreignId('user_id')->constrained('users')->comment('พนักงานผู้ขาย');
            $table->decimal('subtotal', 12, 2)->default(0)->comment('ยอดรวมก่อนส่วนลด');
            $table->decimal('discount', 12, 2)->default(0)->comment('ส่วนลดรวม');
            $table->decimal('member_discount', 12, 2)->default(0)->comment('ส่วนลดจากระดับสมาชิก');
            $table->decimal('vat', 12, 2)->default(0)->comment('ภาษีมูลค่าเพิ่ม');
            $table->decimal('total', 12, 2)->default(0)->comment('ยอดสุทธิที่ต้องชำระ');
            $table->integer('points_earned')->default(0)->comment('แต้มที่ได้รับจากบิลนี้');
            $table->enum('status', ['pending', 'paid', 'void'])
                  ->default('pending')
                  ->comment('pending=รอชำระ, paid=ชำระแล้ว, void=ยกเลิกบิล');
            $table->string('note')->nullable()->comment('หมายเหตุ');
            $table->timestamp('sold_at')->nullable()->comment('เวลาที่ปิดการขาย');
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('sold_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
