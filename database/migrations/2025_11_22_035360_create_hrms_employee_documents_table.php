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
        Schema::create('hrms_employee_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('file',150)->unique();
            $table->unsignedBigInteger('employee_id');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('hrms_employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrms_employee_documents');
    }
};
