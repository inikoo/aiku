<?php

use App\Stubs\Migrations\HasFulfilmentStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasFulfilmentStats;

    public function up()
    {
        Schema::create('group_fulfilment_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table = $this->containerFulfilmentStats($table);
            $table = $this->fulfilmentStats($table);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('group_fulfilment_stats');
    }
};
