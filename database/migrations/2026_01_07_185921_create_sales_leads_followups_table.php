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
        Schema::create('sales_leads_followups', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('inquiry_id');
            $table->date('date');
            $table->date('follow_up_date')->nullable();

            $table->text('remarks')->nullable();
            $table->boolean('is_complete')->default(false);

            $table->timestamps();

            $table->foreign('inquiry_id')->references('id')->on('sales_leads_inquiries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_leads_followups');
    }
};
