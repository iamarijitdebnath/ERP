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
        Schema::create('system_menu_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('color', 6)->nullable()->default(null);
            $table->integer('sequence')->default(1);
            $table->boolean('is_active')->default(false);
            $table->uuid('module_id');
            $table->timestamps();
            
            $table->foreign('module_id')->references('id')->on('system_modules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_menu_groups');
    }
};
