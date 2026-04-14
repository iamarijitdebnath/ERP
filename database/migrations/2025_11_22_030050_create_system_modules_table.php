<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void {
        Schema::create('system_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug', 20)->unique();
            $table->string('icon', 50)->nullable();
            $table->integer('sequence')->default(1);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('system_modules');
    }
};
