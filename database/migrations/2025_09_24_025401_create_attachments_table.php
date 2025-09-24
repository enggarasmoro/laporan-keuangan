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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->nullable()->constrained('transaksis')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama_file');
            $table->string('nama_file_asli');
            $table->string('path_file');
            $table->enum('tipe_file', ['image', 'audio', 'document']);
            $table->string('mime_type');
            $table->unsignedBigInteger('ukuran_file'); // in bytes
            $table->enum('status_ocr', ['pending', 'processing', 'completed', 'failed', 'not_applicable'])->default('not_applicable');
            $table->text('deskripsi')->nullable();
            $table->json('metadata')->nullable(); // For storing OCR confidence, audio duration, etc.
            $table->timestamps();

            // Indexes
            $table->index(['transaksi_id']);
            $table->index(['user_id', 'tipe_file']);
            $table->index(['status_ocr']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
