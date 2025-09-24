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
        Schema::create('ocr_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attachment_id')->constrained('attachments')->cascadeOnDelete();
            $table->text('raw_text'); // Full extracted text
            $table->json('structured_data')->nullable(); // Parsed data (amount, merchant, date, etc.)
            $table->decimal('confidence_score', 5, 2)->nullable(); // OCR confidence percentage
            $table->json('detected_fields')->nullable(); // Specific fields detected (amount, date, merchant, etc.)
            $table->decimal('extracted_amount', 15, 2)->nullable();
            $table->string('extracted_merchant')->nullable();
            $table->date('extracted_date')->nullable();
            $table->time('extracted_time')->nullable();
            $table->string('extracted_reference')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'auto_verified'])->default('pending');
            $table->text('correction_notes')->nullable(); // For manual corrections
            $table->timestamps();

            // Indexes
            $table->index(['attachment_id']);
            $table->index(['verification_status']);
            $table->index(['extracted_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocr_results');
    }
};
