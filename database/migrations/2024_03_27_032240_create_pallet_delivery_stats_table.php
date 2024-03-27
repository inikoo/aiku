<?php

use App\Stubs\Migrations\HasFulfilmentStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasFulfilmentStats;

    public function up()
    {
        Schema::create('pallet_delivery_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('pallet_delivery_id');
            $table->foreign('pallet_delivery_id')->references('id')->on('pallet_deliveries')->onUpdate('cascade')->onDelete('cascade');
            $table = $this->fulfilmentStats($table);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('pallet_delivery_stats');
    }
};
