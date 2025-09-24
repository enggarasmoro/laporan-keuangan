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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('kategori_id');
            $table->unsignedBigInteger('akun_id')->nullable();
            $table->enum('tipe', ['pemasukan', 'pengeluaran']);
            $table->decimal('jumlah', 15, 2);
            $table->string('deskripsi');
            $table->text('catatan')->nullable();
            $table->date('tanggal');
            $table->time('waktu')->nullable();
            $table->string('referensi')->nullable(); // For transaction reference/receipt number
            $table->enum('sumber_input', ['manual', 'ocr', 'audio', 'import'])->default('manual');
            $table->boolean('verified')->default(false); // For OCR/audio verification
            $table->json('metadata')->nullable(); // For additional data like location, tags, OCR confidence, etc.
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'tanggal']);
            $table->index(['tipe', 'tanggal']);
            $table->index(['kategori_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
