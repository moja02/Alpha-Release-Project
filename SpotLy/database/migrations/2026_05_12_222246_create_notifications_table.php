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
            // إنشاء جدول التنبيهات والإشعارات الفورية
            Schema::create('notifications', function (Blueprint $table) {
                $table->id(); // notificationId
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // userId
                $table->text('message'); // message
                $table->string('type')->nullable(); // notificationType
                $table->timestamp('sent_at')->useCurrent(); // sentAt
                $table->timestamps();
            });
        } catch (\Exception $exception) {
            Log::error('Error creating notifications table: ' . $exception->getMessage());
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('notifications');
        } catch (\Exception $exception) {
            Log::error('Error dropping notifications table: ' . $exception->getMessage());
        }
    }
};