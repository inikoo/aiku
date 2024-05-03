<?php

use App\Stubs\Migrations\HasDateIntervalsStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDateIntervalsStats;

    public function up()
    {
        Schema::create('organisation_orders_dashboards', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');

            $table=$this->dateIntervals($table, ['in_baskets', 'in_process', 'in_process_paid', 'in_warehouse', 'packed', 'in_dispatch_area', 'delivery_notes']);

            $table->timestampsTz();
            $table->unique(['organisation_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('organisation_orders_dashboards');
    }
};
