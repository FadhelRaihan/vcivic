<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Memperbarui CHECK constraint pada kolom 'type' di tabel meeting_contents
     * untuk menambahkan value 'infografis'.
     */
    public function up(): void
    {
        // Drop constraint lama dan buat ulang dengan value baru
        DB::statement("ALTER TABLE meeting_contents DROP CONSTRAINT IF EXISTS meeting_contents_type_check");
        DB::statement("ALTER TABLE meeting_contents ADD CONSTRAINT meeting_contents_type_check CHECK (type::text = ANY (ARRAY['pdf'::text, 'ppt'::text, 'video'::text, 'infografis'::text]))");
    }

    /**
     * Rollback: kembalikan CHECK constraint ke value semula (tanpa 'infografis').
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE meeting_contents DROP CONSTRAINT IF EXISTS meeting_contents_type_check");
        DB::statement("ALTER TABLE meeting_contents ADD CONSTRAINT meeting_contents_type_check CHECK (type::text = ANY (ARRAY['pdf'::text, 'ppt'::text, 'video'::text]))");
    }
};
