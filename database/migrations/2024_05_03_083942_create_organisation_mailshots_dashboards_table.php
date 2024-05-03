<?php

use App\Stubs\Migrations\HasDateIntervalsStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDateIntervalsStats;

    public function up()
    {
        Schema::create('organisation_mailshots_dashboards', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');

            $table=$this->dateIntervals($table, ['newsletters', 'marketing_emails', 'abandoned_carts', 'total_mailshots', 'total_emails']);

            $table->timestampsTz();
            $table->unique(['organisation_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('organisation_mailshots_dashboards');
    }
};
