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
        Schema::create('hrms_employee_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('menu_id');
            $table->unsignedBigInteger('employee_id');

            $table->boolean('can_create')->default(false);
            $table->boolean('can_read')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);

            $table->timestamps();

            $table->foreign('menu_id')->references('id')->on('system_menus')->onDelete('cascade');

            $table->foreign('employee_id')->references('id')->on('hrms_employees')->onDelete('cascade');

            $table->unique(['employee_id', 'menu_id']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrms_employee_permissions');
    }
};
