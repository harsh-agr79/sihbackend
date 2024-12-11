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
        Schema::table('communities', function (Blueprint $table) {
            $table->unsignedBigInteger('domain_id')->nullable()->after('id'); // Assuming 'id' exists
            $table->json('subdomains')->nullable()->after('domain_id');

            // Foreign key constraint for domain_id
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            //
        });
    }
};
