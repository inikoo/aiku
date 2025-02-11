<?php

use App\Enums\Fulfilment\PalletStoredItem\PalletStoredItemStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pallet_stored_items', function (Blueprint $table) {
            $table->string('state')->default(PalletStoredItemStateEnum::IN_PROCESS)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pallet_stored_items', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};
