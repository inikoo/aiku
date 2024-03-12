<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('stored_item_returns', function (Blueprint $table) {
            $table->id();
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('stored_item_returns');
    }
};
