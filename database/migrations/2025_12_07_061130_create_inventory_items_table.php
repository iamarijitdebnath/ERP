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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 100);
            $table->string('sku', 100)->nullable();
            $table->string('name', 255);
            $table->uuid('group_id')->nullable();
            $table->text('description')->nullable();
            $table->json('attributes')->nullable();
            $table->uuid('uom_id')->nullable();
            $table->enum('acquire', ['purchase', 'manufacture'])->default('purchase');
            $table->string('barcode', 150)->nullable();
            $table->boolean('has_expiry')->default(false);
            $table->enum('tracking', ['not-applicable', 'batch', 'batch-lot'])->default('not-applicable');
            $table->boolean('is_active')->default(true);
            $table->uuid('master_id')->nullable();
            $table->uuid('company_id')->nullable();

            $table->timestamps();



            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'sku']);
            $table->foreign('group_id')->on('inventory_item_groups')->references('id')->onDelete('set null');
            $table->foreign('uom_id')->on('inventory_uoms')->references('id')->onDelete('set null');
            $table->foreign('company_id')->on('system_companies')->references('id')->onDelete('set null');
            $table->foreign('master_id')->on('inventory_items')->references('id')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
