<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('stored_item_return_stored_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('stored_item_return_id')->index();
            $table->foreign('stored_item_return_id')->references('id')->on('stored_item_returns')->onDelete('cascade');

            $table->unsignedInteger('stored_item_id')->index();
            $table->foreign('stored_item_id')->references('id')->on('stored_items')->onDelete('cascade');

            $table->decimal('quantity');

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('stored_item_return_stored_items');
    }
};
