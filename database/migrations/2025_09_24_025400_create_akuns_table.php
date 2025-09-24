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
        Schema::create('akuns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->enum('tipe', ['bank', 'kas', 'e-wallet', 'investasi', 'kredit']);
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('saldo_saat_ini', 15, 2)->default(0);
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_bank')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('warna', 7)->default('#6B7280');
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'aktif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akuns');
    }
};
