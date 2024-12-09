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
        Schema::create('hack_contests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('problem_statement')->nullable();
            $table->text('evaluation_criteria')->nullable();
            $table->string('eligibility')->nullable();
            $table->datetime('start_date_time'); // Use DATETIME instead of TIMESTAMP
            $table->datetime('end_date_time');   // Use DATETIME instead of TIMESTAMP
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hack_contests');
    }
};
