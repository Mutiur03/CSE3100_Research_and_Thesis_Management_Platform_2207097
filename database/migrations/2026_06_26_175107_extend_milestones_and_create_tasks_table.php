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
        Schema::table('milestones', function (Blueprint $table) {
            $table->unsignedTinyInteger('progress_percentage')->default(0)->after('status');
            $table->foreignId('depends_on_id')->nullable()->after('sort_order')->constrained('milestones')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('depends_on_id')->constrained('users')->nullOnDelete();
        });

        Schema::create('milestone_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('todo');
            $table->string('priority')->default('medium');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['milestone_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestone_tasks');

        Schema::table('milestones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('depends_on_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('progress_percentage');
        });
    }
};
