<?php

use App\Enums\Fulfilment\PalletReturn\PalletReturnItemStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('pallet_return_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pallet_id');
            $table->foreign('pallet_id')->references('id')->on('pallets');

            $table->unsignedBigInteger('pallet_return_id');
            $table->foreign('pallet_return_id')->references('id')->on('pallet_returns');

            $table->string('state')->default(PalletReturnItemStateEnum::IN_PROCESS->value);

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('pallet_return_items');
    }
};
