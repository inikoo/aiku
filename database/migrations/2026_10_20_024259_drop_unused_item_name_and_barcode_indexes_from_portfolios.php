<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropIndex(['item_name']);
            $table->dropIndex(['barcode']);
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->index('item_name');
            $table->index('barcode');
        });
    }
};
