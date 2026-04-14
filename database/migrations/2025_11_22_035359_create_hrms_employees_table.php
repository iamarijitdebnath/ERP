<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('hrms_employees', function (Blueprint $table) {
            $table->id();
            $table->enum('salutation', ['Mr', 'Ms', 'Mrs']);
            $table->string('first_name', 100);
            $table->string('last_name', 100);

            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            $table->string('code', 20)->unique(); 

            $table->enum('employment_type', ['internship', 'contractual', 'part-time', 'full-time'])
                  ->default('full-time');

            $table->enum('payment_type', ['salary', 'wage'])->default('salary');

            $table->string('email', 150)->unique();
            $table->string('password');

            $table->date('date_of_birth')->nullable();
            $table->date('date_of_joining')->nullable();

            $table->integer('notice_period')->nullable();

            $table->date('date_of_resignation')->nullable();
            $table->date('date_of_release')->nullable();

            $table->uuid('department_id')->nullable();
            $table->unsignedBigInteger('under_id')->nullable();  
            $table->uuid('company_id')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable()->default(null);

            $table->unique(['company_id', 'code']);

            $table->foreign('department_id')->references('id')->on('hrms_departments')->onDelete('set null');
            $table->foreign('under_id')->references('id')->on('hrms_employees')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('system_companies')->onDelete('set null');

        });
    }

    public function down(): void {
        Schema::dropIfExists('hrms_employees');
    }
};
