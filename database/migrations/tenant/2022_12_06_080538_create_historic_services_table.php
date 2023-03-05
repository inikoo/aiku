<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 16:11:24 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('historic_services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->boolean('status')->index();
            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('deleted_at')->nullable();
            $table->foreignid('service_id')->constrained();
            $table->unsignedDecimal('price', 18)->comment('unit price');
            $table->string('code')->nullable();
            $table->string('name', 255)->nullable();

            // $table->unsignedSmallInteger('currency_id')->nullable();
            // $table->foreign('currency_id')->references('id')->on('central.currencies');
            $table->unsignedBigInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('historic_services');
    }
};
