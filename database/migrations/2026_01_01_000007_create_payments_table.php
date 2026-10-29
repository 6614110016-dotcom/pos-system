<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * โมดูลจ่ายเงิน (Payment)
 * รองรับการชำระหลายช่องทางในบิลเดียว (แยกชำระ)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 30)->unique()->comment('เลขที่การชำระเงิน');
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->enum('method', ['cash', 'transfer', 'promptpay', 'credit_card', 'e_wallet'])
                  ->default('cash')
                  ->comment('ช่องทางชำระเงิน');
            $table->decimal('amount', 12, 2)->comment('ยอดที่ต้องชำระด้วยช่องทางนี้');
            $table->decimal('received', 12, 2)->default(0)->comment('เงินที่รับมา (กรณีเงินสด)');
            $table->decimal('change_amount', 12, 2)->default(0)->comment('เงินทอน');
            $table->string('reference_no', 100)->nullable()->comment('เลขอ้างอิง/เลขอนุมัติ');
            $table->string('slip_image')->nullable()->comment('ไฟล์สลิปโอนเงิน');
            $table->enum('status', ['pending', 'success', 'refunded'])->default('success');
            $table->foreignId('user_id')->constrained('users')->comment('พนักงานผู้รับชำระ');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('method');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
