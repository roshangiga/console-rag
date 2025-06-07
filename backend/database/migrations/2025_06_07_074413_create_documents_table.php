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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('directory_id');
            $table->string('file_path');
            $table->string('version');
            $table->enum('type', ['QA', 'Image', 'Process_Chart', 'Presentation', 'General_Doc']);
            $table->text('purpose');
            $table->json('metadata')->nullable();
            $table->enum('status', ['SUCCESS', 'FAILED', 'UPDATING']);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('directory_id')->references('id')->on('directories')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
