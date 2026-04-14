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
        Schema::create('inventory_goods_receipt_note', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('doc_no', 30);
            $table->uuid('supplier_id')->nullable();
            $table->uuid('purchase_order_id')->nullable();
            $table->string('purchase_order_no', 30)->nullable();
            $table->date('date');
            $table->string('remarks', 400)->nullable();
            $table->uuid('received_by')->nullable();
            $table->enum('status', ['draft', 'active', 'cancel'])
                  ->default('draft');
            $table->uuid('company_id')->nullable();
            $table->uuid('transaction_id')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'doc_no']);
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('purchase_suppliers')
                  ->onDelete('set null');          
             $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('purchase_orders')
                  ->onDelete('set null');
            $table->foreign('received_by')
                  ->references('id')
                  ->on('hrms_employees')
                  ->onDelete('set null');
            $table->foreign('company_id')
                  ->references('id')
                  ->on('system_companies')
                  ->onDelete('set null');
            $table->foreign('transaction_id')
                  ->references('id')
                  ->on('inventory_transaction')
                  ->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_goods_receipt_note');
    }
};
