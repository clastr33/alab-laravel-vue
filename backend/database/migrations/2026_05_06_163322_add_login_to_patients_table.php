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
        Schema::table('patients', function (Blueprint $table) {
            // Nullable for SQLite compatibility when adding to existing table.
            $table->string('login')->nullable()->after('id');
        });

        // In a real DB we would backfill + then add NOT NULL + unique constraint in a follow-up migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('login');
        });
    }
};
