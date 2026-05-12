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
            // إنشاء جدول طلبات شحن الرصيد المرفقة بالإيصالات
            Schema::create('recharge_requests', function (Blueprint $table) {
                $table->id(); // requestId
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // userId
                $table->integer('requested_points'); // requestedPoints
                $table->string('receipt_file'); // receiptFile
                $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending'); // status
                $table->timestamps();
            });
        } catch (\Exception $exception) {
            Log::error('Error creating recharge_requests table: ' . $exception->getMessage());
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('recharge_requests');
        } catch (\Exception $exception) {
            Log::error('Error dropping recharge_requests table: ' . $exception->getMessage());
        }
    }
};
