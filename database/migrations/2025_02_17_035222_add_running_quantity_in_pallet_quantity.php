<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stored_item_movements', function (Blueprint $table) {
            $table->decimal('running_quantity', 8, 2)->default(0)->after('quantity');
            $table->decimal('running_in_pallet_quantity', 8, 2)->default(0)->after('running_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stored_item_movements', function (Blueprint $table) {
            $table->dropColumn('running_quantity');
            $table->dropColumn('running_in_pallet_quantity');
        });
    }
};
