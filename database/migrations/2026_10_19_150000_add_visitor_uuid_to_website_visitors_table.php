<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The browser's own id, from the `aiku_vid` cookie. `visitor_hash` is built from the IP address
     * and the user agent, so the same browser gets a new hash every time it moves between WiFi and
     * mobile data and one customer's visits scatter over several hashes. The uuid stays with the
     * browser, so those visits can still be tied together.
     */
    public function up(): void
    {
        Schema::table('website_visitors', function (Blueprint $table) {
            $table->uuid('visitor_uuid')->nullable()->after('visitor_hash');
            $table->index(['website_id', 'visitor_uuid']);
        });
    }

    public function down(): void
    {
        Schema::table('website_visitors', function (Blueprint $table) {
            $table->dropIndex(['website_id', 'visitor_uuid']);
            $table->dropColumn('visitor_uuid');
        });
    }
};
