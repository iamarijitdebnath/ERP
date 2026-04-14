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
        Schema::create('sales_leads_inquiries', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('product_id')->nullable();
            $table->uuid('employee_id')->nullable();
            $table->uuid('lead_id');

            $table->string('source', 50)->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('inventory_items')->onDelete('set null');
            $table->foreign('employee_id')->references('id')->on('hrms_employees')->onDelete('set null');
            $table->foreign('lead_id')->references('id')->on('sales_leads');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_leads_inquiries');
    }
};
