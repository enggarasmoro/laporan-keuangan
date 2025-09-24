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
        Schema::table('akuns', function (Blueprint $table) {
            $table->string('nama_ewallet')->nullable()->after('nama_bank');
            $table->string('nomor_hp')->nullable()->after('nama_ewallet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akuns', function (Blueprint $table) {
            $table->dropColumn(['nama_ewallet', 'nomor_hp']);
        });
    }
};
