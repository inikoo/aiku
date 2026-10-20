<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::statement("
            CREATE INDEX webpages_site_canonical_path_live_idx ON webpages
            (website_id, (substring(canonical_url from 'https?://[^/]+(/.*)$')))
            WHERE state = 'live' AND deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX webpages_site_canonical_path_live_idx');
    }
};
