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
        Schema::create('system_login_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('email', 150);
            $table->integer('status');
            $table->string('ip_address', 50);

            $table->unsignedBigInteger('employee_id')->nullable();

            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('hrms_employees')->onDelete('set null'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_login_logs');
    }
};
