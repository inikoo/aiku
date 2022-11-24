<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestLoggingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('request-logging.database-logging.table'), function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->double('duration', 14, 7)->nullable();
            $table->integer('status')->nullable();
            $table->string('method');
            $table->text('uri');
            $table->longText('body')->nullable();
            $table->integer('request_size')->nullable();
            $table->longText('files')->nullable();
            $table->longText('response')->nullable();
            $table->integer('response_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('request-logging.database-logging.table'));
    }
}
