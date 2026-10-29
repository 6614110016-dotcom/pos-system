<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * โมดูลสินค้า (Stock) - ประวัติการเคลื่อนไหวสต็อก
 * ทุกครั้งที่สต็อกเปลี่ยน (รับเข้า / ขายออก / ปรับยอด / คืนสินค้า) จะบันทึกที่ตารางนี้
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjust', 'return'])
                  ->comment('in=รับเข้า, out=ตัดออก(ขาย), adjust=ปรับยอด, return=รับคืน');
            $table->integer('qty')->comment('จำนวนที่เปลี่ยนแปลง (+/-)');
            $table->integer('qty_before')->default(0)->comment('ยอดคงเหลือก่อนทำรายการ');
            $table->integer('qty_after')->default(0)->comment('ยอดคงเหลือหลังทำรายการ');
            $table->decimal('cost_price', 10, 2)->default(0)->comment('ราคาทุนต่อหน่วยขณะรับเข้า');
            $table->string('reference', 50)->nullable()->comment('เลขที่เอกสารอ้างอิง เช่น เลขที่บิลขาย');
            $table->string('note')->nullable()->comment('หมายเหตุ');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()
                  ->comment('ผู้ทำรายการ');
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
