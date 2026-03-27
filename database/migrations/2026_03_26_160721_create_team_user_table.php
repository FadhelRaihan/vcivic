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
        Schema::create('team_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id');
            $table->foreignUuid('user_id');
            $table->string('role')->nullable();
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });

        DB::statement('ALTER TABLE team_user ENABLE ROW LEVEL SECURITY;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }
};
