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
        Schema::create('debt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            // $table->float('amount', 15, 2);
            //            $table->float('paid_amount', 15, 2)->default(0);
            //            $table->float('remaining_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 10, 2);
            // $table->longText('remaining_amount')->nullable();
            $table->date('paid_at');
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
        Schema::dropIfExists('debt_items');
    }
};
