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
        Schema::create('audio_transcriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attachment_id')->constrained('attachments')->cascadeOnDelete();
            $table->text('transcribed_text'); // Full transcribed text
            $table->json('structured_data')->nullable(); // Parsed financial data from audio
            $table->decimal('confidence_score', 5, 2)->nullable(); // Transcription confidence
            $table->string('language_detected', 10)->default('id'); // Language code
            $table->integer('duration_seconds')->nullable();
            $table->json('detected_entities')->nullable(); // Names, amounts, dates mentioned
            $table->decimal('extracted_amount', 15, 2)->nullable();
            $table->string('extracted_description')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'auto_verified'])->default('pending');
            $table->text('correction_notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['attachment_id']);
            $table->index(['verification_status']);
            $table->index(['language_detected']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_transcriptions');
    }
};