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
            // إنشاء جدول الحجوزات المبدئية والفعلية
            Schema::create('bookings', function (Blueprint $table) {
                $table->id(); // bookingId
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // userId
                $table->foreignId('parking_id')->constrained('parkings')->onDelete('cascade'); // parkingId
                $table->string('plate_number'); // plateNumber
                $table->dateTime('start_time'); // startTime
                $table->dateTime('end_time'); // endTime
                $table->enum('type', ['initial', 'actual']); // bookingType
                $table->enum('status', ['confirmed', 'cancelled', 'auto_cancelled'])->default('confirmed'); // status
                $table->timestamps();
            });
        } catch (\Exception $exception) {
            Log::error('Error creating bookings table: ' . $exception->getMessage());
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('bookings');
        } catch (\Exception $exception) {
            Log::error('Error dropping bookings table: ' . $exception->getMessage());
        }
    }
};
