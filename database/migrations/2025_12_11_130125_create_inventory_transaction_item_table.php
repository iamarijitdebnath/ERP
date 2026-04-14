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
        Schema::create('inventory_transaction_item', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('item_id')->nullable();
            $table->uuid('uom_id')->nullable();
            $table->decimal('quantity', 15, 4);

            $table->decimal('price', 14, 2)->nullable();
            $table->decimal('cgst', 5, 2)->nullable();
            $table->decimal('sgst', 5, 2)->nullable();
            $table->decimal('igst', 5, 2)->nullable();
            $table->decimal('cess', 5, 2)->nullable();

            $table->string('batch_no')->nullable();
            $table->string('lot_no')->nullable();
            $table->string('serial_no')->nullable();
            $table->date('exp_date')->nullable();

            $table->uuid('inventory_transaction_id')->nullable();

            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('inventory_items')->onDelete('set null');

            $table->foreign('uom_id')->references('id')->on('inventory_uoms')->onDelete('set null');

            $table->foreign('inventory_transaction_id')->references('id')->on('inventory_transaction')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transaction_item');
    }
};
