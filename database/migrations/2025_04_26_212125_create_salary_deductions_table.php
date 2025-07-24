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
        Schema::create('salary_deductions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('type');
            $table->decimal('value', 10, 2);
            $table->tinyInteger('created_by');
            $table->tinyInteger('updated_by')->nullable();
            $table->tinyInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_deductions');
    }
};
