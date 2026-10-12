<?php

/*
 * Author: eka yudinata (https://github.com/ekayudinata)
 * Created: Monday, 12 Oct 2026 12:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, eka yudinata
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mailshots', function (Blueprint $table) {
            $table->foreignId('ab_test_variant_of_id')->nullable()->index()->constrained('mailshots');
            $table->unsignedSmallInteger('ab_test_size_percentage')->default(100);
            $table->foreignId('ab_test_winner_mailshot_id')->nullable()->constrained('mailshots');
            $table->dateTimeTz('ab_test_winner_sent_at')->nullable();
            $table->string('sender_name')->nullable();
        });
    }


    public function down(): void
    {
        Schema::table('mailshots', function (Blueprint $table) {
            $table->dropForeign(['ab_test_variant_of_id']);
            $table->dropForeign(['ab_test_winner_mailshot_id']);
            $table->dropColumn(['ab_test_variant_of_id', 'ab_test_size_percentage', 'ab_test_winner_mailshot_id', 'ab_test_winner_sent_at', 'sender_name']);
        });
    }
};
