<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'parking_id')) {
                $table->foreignId('parking_id')->nullable()->constrained('parkings')->onDelete('set null')->after('account_id');
            }
            if (!Schema::hasColumn('employees', 'shift_role')) {
                $table->string('shift_role')->nullable()->default('موظف ميداني')->after('parking_id');
            }
        });

        // Migrate any existing parkings.employee_id assignments into employees.parking_id
        try {
            $parkings = \Illuminate\Support\Facades\DB::table('parkings')->whereNotNull('employee_id')->get();
            foreach ($parkings as $p) {
                \Illuminate\Support\Facades\DB::table('employees')
                    ->where('id', $p->employee_id)
                    ->update(['parking_id' => $p->id]);
            }
        } catch (\Exception $e) {
            // Ignore if data migration is not applicable
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'parking_id')) {
                $table->dropForeign(['parking_id']);
                $table->dropColumn('parking_id');
            }
            if (Schema::hasColumn('employees', 'shift_role')) {
                $table->dropColumn('shift_role');
            }
        });
    }
};
