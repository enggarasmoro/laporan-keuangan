<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the first user ID that exists
        $firstUser = DB::table('users')->first();
        if (!$firstUser) {
            throw new \Exception('No users found in database. Please create a user first.');
        }

        $firstUserId = $firstUser->id;

        // Set user_id to the first user's ID for existing records that don't have user_id
        DB::table('kategoris')->whereNull('user_id')->update(['user_id' => $firstUserId]);

        // Fix orphaned records - set invalid user_id to first user
        $validUserIds = DB::table('users')->pluck('id')->toArray();
        DB::table('kategoris')
            ->whereNotIn('user_id', $validUserIds)
            ->update(['user_id' => $firstUserId]);

        Schema::table('kategoris', function (Blueprint $table) {
            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Update indexes - drop old ones if they exist and add new ones
            try {
                $table->dropIndex(['tipe', 'aktif']);
            } catch (\Exception $e) {
                // Index might not exist, continue
            }

            $table->index(['user_id', 'tipe', 'aktif']);
            $table->index(['user_id', 'aktif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategoris', function (Blueprint $table) {
            // Drop indexes first
            try {
                $table->dropIndex(['user_id', 'tipe', 'aktif']);
            } catch (\Exception $e) {
                // Index might not exist
            }

            try {
                $table->dropIndex(['user_id', 'aktif']);
            } catch (\Exception $e) {
                // Index might not exist
            }

            // Drop foreign key constraint
            $table->dropForeign(['user_id']);

            // Restore original index
            $table->index(['tipe', 'aktif']);
        });
    }
};
