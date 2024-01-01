<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 10 Apr 2023 09:45:14 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('media_order', function (Blueprint $table) {
            $table->unsignedInteger('order_id')->index();
            $table->string('type')->index();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->unsignedInteger('media_id')->index();
            $table->unique(['order_id', 'media_id']);
            $table->string('owner_type')->index();
            $table->unsignedInteger('owner_id');
            $table->boolean('public')->default(false)->index();

            $table->timestampsTz();
            $table->index(['owner_type', 'owner_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('media_order');
    }
};
