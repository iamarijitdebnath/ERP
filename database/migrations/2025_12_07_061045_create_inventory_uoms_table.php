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
        Schema::create('inventory_uoms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name',100);
            $table->string('category',50)->nullable();
            $table->decimal('si_unit',14,2)->default(1);
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
        Schema::dropIfExists('inventory_uoms');
    }
};
