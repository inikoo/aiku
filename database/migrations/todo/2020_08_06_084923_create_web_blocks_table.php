<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateWebBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_blocks', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('webpage_id');
            $table->foreign('webpage_id')->references('id')->on('webpages');
            $table->string('slug');
            $table->json('settings');
            $table->json('data');
            $table->timestampsTz();
            $table->unsignedMediumInteger('legacy_id')->nullable();
            $table->index(['tenant_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_blocks');
    }
}
