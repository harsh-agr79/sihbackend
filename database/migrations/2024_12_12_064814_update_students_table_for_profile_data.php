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
        Schema::table('students', function (Blueprint $table) {
            // Adding JSON columns for profile data
            $table->json('education')->nullable()->after('grade'); // Store education details
            $table->json('experience')->nullable()->after('education'); // Store work experience
            $table->json('skills')->nullable()->after('experience'); // Store skills
            $table->json('hobbies')->nullable()->after('skills'); // Store hobbies
            $table->json('domains')->nullable()->after('hobbies'); // Store interested domains
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
