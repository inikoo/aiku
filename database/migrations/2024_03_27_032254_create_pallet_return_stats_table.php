<?php

use App\Stubs\Migrations\HasFulfilmentStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasFulfilmentStats;

    public function up()
    {
        Schema::create('pallet_return_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('pallet_return_id');
            $table->foreign('pallet_return_id')->references('id')->on('pallet_returns')->onUpdate('cascade')->onDelete('cascade');
            $table = $this->fulfilmentStats($table);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('pallet_return_stats');
    }
};
