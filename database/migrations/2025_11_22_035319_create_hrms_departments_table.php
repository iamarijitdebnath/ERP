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
        Schema::create('hrms_departments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name', 100);
            $table->string('code', 20)->unique();

            $table->uuid('company_id')->nullable();

            $table->timestamps();
            $table->unique(['company_id', 'code']);
            $table->foreign('company_id')->references('id')->on('system_companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrms_employee_departments');
    }
};
