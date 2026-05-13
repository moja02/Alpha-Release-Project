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
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                
                // المفتاح الأجنبي يربط الكلاس الابن بالكلاس الأب Account
                $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
                
                // الخصائص الخاصة بالـ Employee
                $table->string('bank_account_number'); // bankAccountNumber
                
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Error creating employees table: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
