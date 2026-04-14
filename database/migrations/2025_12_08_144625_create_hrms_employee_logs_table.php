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
        Schema::create('hrms_employee_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('menu_id')->nullable();
            $table->uuid('unique_id')->nullable();
            $table->enum('operation', ['note', 'create', 'update', 'delete']);
            $table->json('data_old')->nullable();
            $table->json('data_new')->nullable();
            $table->string('note', 400)->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();

            $table->timestamps();

            $table->foreign('menu_id')->references('id')->on('system_menus')->onDelete('set null');
            $table->foreign('employee_id')->references('id')->on('hrms_employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrms_employee_logs');
    }
};
