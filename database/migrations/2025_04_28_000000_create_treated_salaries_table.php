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
        Schema::create('treated_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('treatment_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_working_days');
            $table->integer('actual_working_days');
            $table->integer('total_working_hours');
            $table->integer('actual_working_hours');
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('base_salary', 10, 2);
            $table->decimal('total_bonuses', 10, 2);
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('total_recoveries', 10, 2)->default(0);
            $table->decimal('final_salary', 10, 2);
            $table->json('bonus_details')->nullable();
            $table->json('deduction_details')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->date('paid_at')->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Ensure we don't process the same employee twice for the same period
            $table->unique(['employee_id', 'start_date', 'end_date', 'deleted_at'], 'unique_employee_salary_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treated_salaries');
    }
};
