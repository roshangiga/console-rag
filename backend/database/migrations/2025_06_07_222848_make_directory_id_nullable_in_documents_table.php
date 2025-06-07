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
        Schema::table('documents', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['directory_id']);

            // Make directory_id nullable
            $table->unsignedBigInteger('directory_id')->nullable()->change();

            // Re-add the foreign key constraint with nullable support
            $table->foreign('directory_id')->references('id')->on('directories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['directory_id']);

            // Make directory_id non-nullable again
            $table->unsignedBigInteger('directory_id')->nullable(false)->change();

            // Re-add the original foreign key constraint
            $table->foreign('directory_id')->references('id')->on('directories')->onDelete('cascade');
        });
    }
};
