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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 50);
            $table->bigInteger('nni')->nullable();
            $table->string('cnps_no', 50)->nullable();
            $table->string('name');
            $table->string('surname');
            $table->bigInteger('phone')->nullable();
            $table->string('email', 50)->nullable();
            $table->integer('gender')->nullable();
            $table->tinyText('country_id')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->integer('marital_status')->nullable();
            $table->integer('children_count')->default(0);
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->foreignId('location_id')
                ->constrained('locations')
                ->onDelete('restrict');
            $table->foreignId('task_id')
                ->constrained('tasks')
                ->onDelete('restrict');
            $table->foreignId('grade_id')
                ->constrained('grades')
                ->onDelete('restrict');
            $table->tinyInteger('employee_type_id');
            $table->tinyInteger('grid_category_id')->nullable();
            $table->tinyInteger('echelon_id')->nullable();
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->date('hiring_date')->nullable();
            $table->date('end_date')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->date('status_start_date')->nullable();
            $table->date('status_end_date')->nullable();
            $table->longText('status_comment')->nullable();
            $table->boolean('on_leave')->default(false);
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique([
                'matricule',
                'deleted_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
