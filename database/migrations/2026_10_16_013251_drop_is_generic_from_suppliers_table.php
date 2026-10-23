<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('suppliers', 'is_generic')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('is_generic');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('suppliers', 'is_generic')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->boolean('is_generic')->default(false)->index();
            });
        }
    }
};
