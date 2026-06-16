<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::create('activity_cash_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('parking_id')->constrained('parkings')->onDelete('cascade');
                $table->enum('operation_type', ['entry', 'exit', 'recharge']);
                $table->string('plate_number')->nullable();
                $table->decimal('cash_value', 10, 2)->default(0.00);
                $table->unsignedBigInteger('driver_account_id')->nullable();
                $table->foreign('driver_account_id')->references('id')->on('accounts')->onDelete('set null');
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Error creating activity_cash_audit_logs table: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_cash_audit_logs');
    }
};
