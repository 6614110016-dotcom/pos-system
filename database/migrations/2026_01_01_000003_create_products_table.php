<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * โมดูลสินค้า (Stock) - ตารางสินค้า
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code', 30)->unique()->comment('รหัสสินค้า');
            $table->string('barcode', 50)->nullable()->unique()->comment('บาร์โค้ด');
            $table->string('name', 200)->comment('ชื่อสินค้า');
            $table->foreignId('category_id')->constrained('categories')->comment('หมวดหมู่');
            $table->text('description')->nullable()->comment('รายละเอียดสินค้า');
            $table->string('unit', 20)->default('ชิ้น')->comment('หน่วยนับ');
            $table->decimal('cost_price', 10, 2)->default(0)->comment('ราคาทุน');
            $table->decimal('price', 10, 2)->default(0)->comment('ราคาขาย');
            $table->integer('stock_qty')->default(0)->comment('จำนวนคงเหลือ');
            $table->integer('reorder_level')->default(10)->comment('จุดสั่งซื้อ (แจ้งเตือนสินค้าใกล้หมด)');
            $table->string('image')->nullable()->comment('รูปสินค้า');
            $table->boolean('status')->default(true)->comment('สถานะการขาย');
            $table->timestamps();

            $table->index('name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
