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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('doc_no', 30);
            $table->uuid('company_id')->nullable();

            $table->unique(['company_id', 'doc_no']);

            $table->foreign('company_id')
                  ->references('id')
                  ->on('system_companies')
                  ->onDelete('set null');

            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
