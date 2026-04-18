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
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('is_template')->default(false)->after('join_code');
            $table->timestamp('last_synced_at')->nullable()->after('is_template');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignUuid('template_meeting_id')->nullable()->constrained('meetings')->nullOnDelete()->after('id');
        });

        Schema::table('meeting_contents', function (Blueprint $table) {
            $table->foreignUuid('template_content_id')->nullable()->constrained('meeting_contents')->nullOnDelete()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_contents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('template_content_id');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('template_meeting_id');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['is_template', 'last_synced_at']);
        });
    }
};
