<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('system_companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('system_companies');
    }
};
