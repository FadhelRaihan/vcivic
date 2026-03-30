<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discussions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            $table->uuid('parent_id')->nullable();

            $table->text('body');
            $table->timestamps();
        });

        Schema::table('discussions', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('discussions')->cascadeOnDelete();
        });

        DB::statement('ALTER TABLE discussions ENABLE ROW LEVEL SECURITY;');
    }

    public function down(): void
    {
        Schema::dropIfExists('discussions');
    }
};