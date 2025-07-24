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
        Schema::create('employee_product_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_product_id')->constrained('employee_products')->restrictOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->bigInteger('product_type_id')->comment('tutma sebibim EmployeeProdcutItemRelationManager.php');
            $table->integer('quantity')->nullable();
            $table->boolean('is_active')->default(1);
            $table->longText('description')->nullable();
            $table->tinyInteger('state')->nullable()->comment('ürünün iade durumunu belirtir');
            $table->integer('state_quantity')->nullable()->comment('ürünün iade durumunda kaç adet olduğunu belirtir');
            $table->longText('state_description')->nullable()->comment('ürünün iade durumunun açıklamasını belirtir');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // $table->unique(['employee_id', 'product_id', 'is_active', 'deleted_at'], 'employee_product_items_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_product_items');
    }
};
