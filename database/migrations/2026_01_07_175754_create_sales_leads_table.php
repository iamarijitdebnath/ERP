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
        Schema::create('sales_leads', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name', 100);
            $table->string('email', 100)->nullable();
            $table->string('mobile', 20)->nullable();

            $table->uuid('customer_id')->nullable();
            $table->uuid('company_id');

            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('system_companies');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_leads');
    }
};
