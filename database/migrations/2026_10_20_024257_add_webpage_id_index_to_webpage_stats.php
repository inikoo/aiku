<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('webpage_stats', function (Blueprint $table) {
            $table->unique('webpage_id');
        });
    }

    public function down(): void
    {
        Schema::table('webpage_stats', function (Blueprint $table) {
            $table->dropUnique(['webpage_id']);
        });
    }
};
