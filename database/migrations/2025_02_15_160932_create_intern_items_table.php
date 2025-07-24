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
        Schema::create('intern_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')
                ->constrained('interns')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->longText('description')->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_items');
    }
};
