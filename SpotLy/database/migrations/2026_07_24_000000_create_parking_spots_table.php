<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('parking_spots')) {
            Schema::create('parking_spots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parking_id')->constrained('parkings')->onDelete('cascade');
                $table->integer('spot_number');
                $table->enum('status', ['available', 'occupied', 'disabled'])->default('available');
                $table->timestamps();

                $table->unique(['parking_id', 'spot_number']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_spots');
    }
};
