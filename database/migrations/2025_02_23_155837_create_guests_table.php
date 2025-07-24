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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('name');
            $table->string('phone');
            $table->integer('gender');
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('company')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('phone', 'deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
