<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * โมดูลการขาย (Sale) - รายการสินค้าในบิล
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->string('product_code', 30)->comment('เก็บรหัสสินค้า ณ เวลาขาย');
            $table->string('product_name', 200)->comment('เก็บชื่อสินค้า ณ เวลาขาย');
            $table->decimal('price', 10, 2)->comment('ราคาขายต่อหน่วย ณ เวลาขาย');
            $table->decimal('cost_price', 10, 2)->default(0)->comment('ราคาทุนต่อหน่วย (ใช้คำนวณกำไร)');
            $table->integer('qty')->comment('จำนวนที่ขาย');
            $table->decimal('discount', 10, 2)->default(0)->comment('ส่วนลดของรายการ');
            $table->decimal('subtotal', 12, 2)->comment('ยอดรวมรายการ = (price * qty) - discount');
            $table->timestamps();

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
