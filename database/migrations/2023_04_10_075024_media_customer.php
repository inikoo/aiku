<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 10 Apr 2023 09:51:06 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('media_customer', function (Blueprint $table) {
            $table->unsignedInteger('customer_id')->index();
            $table->string('type')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedInteger('media_id')->index();
            $table->unique(['customer_id', 'media_id']);
            $table->string('owner_type')->index();
            $table->unsignedInteger('owner_id');
            $table->boolean('public')->default(false)->index();

            $table->timestampsTz();
            $table->index(['owner_type', 'owner_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('media_customer');
    }
};
