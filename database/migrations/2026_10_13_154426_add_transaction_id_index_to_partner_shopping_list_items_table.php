<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('partner_shopping_list_items', function (Blueprint $table) {
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('partner_shopping_list_items', function (Blueprint $table) {
            $table->dropIndex(['transaction_id']);
        });
    }
};
