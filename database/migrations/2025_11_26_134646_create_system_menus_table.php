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
        Schema::create('system_menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 20);
            $table->string('route', 50)->nullable();
            $table->integer('sequence')->default(1);
            $table->boolean('is_active')->default(true);
            $table->uuid('group_id')->nullable(); 
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('system_menu_groups')->onDelete('set null');
            $table->unique(['route', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_menus');
    }
};
