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
        Schema::create('thesis_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->default('other');
            $table->unsignedInteger('current_version')->default(1);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['thesis_id', 'category']);
        });

        Schema::create('thesis_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('file_path', 1000);
            $table->string('file_name');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type', 100);
            $table->text('change_summary')->nullable();
            $table->string('checksum', 64);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['thesis_document_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thesis_document_versions');
        Schema::dropIfExists('thesis_documents');
    }
};
