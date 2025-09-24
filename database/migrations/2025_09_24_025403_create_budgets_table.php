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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategoris')->cascadeOnDelete();
            $table->string('nama');
            $table->decimal('jumlah_budget', 15, 2);
            $table->decimal('jumlah_terpakai', 15, 2)->default(0);
            $table->enum('periode', ['harian', 'mingguan', 'bulanan', 'tahunan']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('notifikasi_aktif')->default(true);
            $table->integer('threshold_peringatan')->default(80); // Percentage for warning notification
            $table->boolean('aktif')->default(true);
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'aktif']);
            $table->index(['kategori_id', 'periode']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
