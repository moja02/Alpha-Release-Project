<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        try {
            // إنشاء جدول المحفظة الرقمية الخاص بكل مستخدم
            Schema::create('wallets', function (Blueprint $table) {
                $table->id(); // walletId
                // ربط المحفظة بالمستخدم (السائق)
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // userId
                $table->decimal('balance', 10, 2)->default(0.00); // balance
                $table->timestamps();
            });
        } catch (\Exception $exception) {
            // تسجيل الخطأ في حال فشل إنشاء الجدول
            Log::error('Error creating wallets table: ' . $exception->getMessage());
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('wallets');
        } catch (\Exception $exception) {
            Log::error('Error dropping wallets table: ' . $exception->getMessage());
        }
    }
};
