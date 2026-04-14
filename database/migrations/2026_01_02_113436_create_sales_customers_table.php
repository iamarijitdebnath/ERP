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
        Schema::create('sales_customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->uuid('company_id')->nullable();
            $table->timestamps();

            $table->foreign('company_id')
                  ->references('id')
                  ->on('system_companies')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_customers');
    }
};
