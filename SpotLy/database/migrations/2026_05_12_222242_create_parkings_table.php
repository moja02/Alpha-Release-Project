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
            // إنشاء جدول مواقف السيارات وتتبع السعة
            Schema::create('parkings', function (Blueprint $table) {
                $table->id(); // parkingId
                $table->string('name'); // name
                $table->string('location_park'); // locationPark
                $table->integer('total_capacity'); // totalCapacity
                $table->integer('available_capacity'); // availableCapacity
                $table->timestamps();
            });
        } catch (\Exception $exception) {
            Log::error('Error creating parkings table: ' . $exception->getMessage());
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('parkings');
        } catch (\Exception $exception) {
            Log::error('Error dropping parkings table: ' . $exception->getMessage());
        }
    }
};