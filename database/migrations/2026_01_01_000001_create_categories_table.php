<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * โมดูลสินค้า (Stock) - ตารางหมวดหมู่สินค้า
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('รหัสหมวดหมู่');
            $table->string('name', 150)->comment('ชื่อหมวดหมู่');
            $table->text('description')->nullable()->comment('คำอธิบาย');
            $table->boolean('status')->default(true)->comment('สถานะการใช้งาน');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
