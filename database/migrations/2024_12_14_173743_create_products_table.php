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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number', 191)->nullable()->unique();
            $table->string('mac_address', 191)->nullable()->unique();
            $table->string('plate_number', 191)->nullable()->unique();
            $table->string('chassis_number', 191)->nullable()->unique();
            $table->integer('quantity')->nullable();
            $table->boolean('is_available')->default(true);
            $table->bigInteger('product_type_id');
            $table->longText('description')->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'serial_number',
                'mac_address',
                'plate_number',
                'chassis_number',
                'deleted_at',
            ], 'unique_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
