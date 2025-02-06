<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('pallet_return_items', function (Blueprint $table) {
            $table->dropForeign(['pallet_stored_item_id']);
            $table->foreign('pallet_stored_item_id')->references('id')->on('pallet_stored_items')->nullOnDelete();
        });
    }


    public function down(): void
    {

    }
};
