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
        Schema::create('inventory_warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name', 150);
            $table->string('code', 50)->unique();

            $table->string('address1', 255);
            $table->string('address2', 255)->nullable();
            $table->string('city', 100);
            $table->string('state', 100);

            $table->string('contact_person', 150)->nullable();
            $table->string('mobile_no', 20)->nullable();
            $table->string('email', 150)->nullable();

            $table->uuid('company_id')->nullable();

            $table->timestamps();

            $table->foreign('company_id')->on('system_companies')->references('id')->onDelete('set null');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_warehouses');
    }
};
