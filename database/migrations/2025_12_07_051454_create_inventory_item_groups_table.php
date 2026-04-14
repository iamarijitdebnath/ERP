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
        Schema::create('inventory_item_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name',150);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('inventory_item_groups');
    }
};
