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
        Schema::create('inventory_goods_delivery_note', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('doc_no', 30);
            $table->uuid('customer_id')->nullable();
            $table->uuid('invoice_id')->nullable();
            $table->date('date');
            $table->string('remarks', 400)->nullable();
            $table->uuid('issued_by')->nullable();
            $table->string('way_bill_no', 30)->nullable();
            $table->enum('status', ['draft', 'active', 'cancel'])
                  ->default('draft');
            $table->uuid('company_id')->nullable();
            $table->uuid('transaction_id')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'doc_no']);
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('sales_customers')
                  ->onDelete('set null');
            $table->foreign('invoice_id')
                  ->references('id')
                  ->on('sales_invoices')
                  ->onDelete('set null');
            $table->foreign('issued_by')
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
        Schema::dropIfExists('inventory_goods_delivery_note');
    }
};
