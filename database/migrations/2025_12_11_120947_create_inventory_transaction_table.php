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
        Schema::create('inventory_transaction', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type',['grn','gdn','entry','adjustment','transfer']);
            $table->uuid('type_id')->nullable();
            $table->uuid('from_warehouse_id')->nullable();
            $table->uuid('to_warehouse_id')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->uuid('company_id')->nullable();
            $table->timestamps();

            $table->foreign('from_warehouse_id')->references('id')->on('inventory_warehouses')->onDelete('set null');
            $table->foreign('to_warehouse_id')->references('id')->on('inventory_warehouses')->onDelete('set null');
            $table->foreign('company_id')->on('system_companies')->references('id')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transaction');
    }
};
