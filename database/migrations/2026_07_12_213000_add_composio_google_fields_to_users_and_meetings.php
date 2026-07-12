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
        Schema::table('users', function (Blueprint $table) {
            $table->string('composio_google_connected_account_id')->nullable()->after('last_login_at');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->string('google_event_id')->nullable()->after('meeting_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('composio_google_connected_account_id');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('google_event_id');
        });
    }
};
