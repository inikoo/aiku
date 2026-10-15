<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('suppliers', 'default_lead_time_days')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('default_lead_time_days');
            });
        }
    }

    public function down(): void
    {
    }
};
