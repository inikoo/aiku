<?php

use App\Stubs\Migrations\HasDateIntervalsStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDateIntervalsStats;

    public function up()
    {
        Schema::create('shop_mailshots_dashboards', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shop_id');
            $table->foreign('shop_id')->references('id')->on('shops')->onUpdate('cascade')->onDelete('cascade');

            $table=$this->dateIntervals($table, ['newsletters', 'marketing_emails', 'abandoned_carts', 'total_mailshots', 'total_emails']);

            $table->timestampsTz();
            $table->unique(['shop_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('shop_mailshots_dashboards');
    }
};
