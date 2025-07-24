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
        Schema::create('guest_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('subject');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->tinyInteger('state')->default(3)->comment('1: En cours, 2: Terminé, 3: En attente');
            $table->tinyInteger('approval')->default(1);
            $table->bigInteger('approved_by')->nullable();
            $table->date('approved_at')->nullable();
            $table->bigInteger('postponed_by')->nullable();
            $table->dateTime('postponed_at')->nullable();
            $table->longText('postponed_reason')->nullable();
            $table->bigInteger('canceled_by')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->longText('cancel_reason')->nullable();
            $table->longText('resume')->nullable();
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
        Schema::dropIfExists('guest_items');
    }
};
