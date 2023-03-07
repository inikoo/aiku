<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 13:22:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create(
            'addresses',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('address_line_1', 255)->nullable();
                $table->string('address_line_2', 255)->nullable();
                $table->string('sorting_code')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('locality')->nullable();
                $table->string('dependant_locality')->nullable();
                $table->string('administrative_area')->nullable();
                $table->string('country_code', 2)->nullable()->index();
                $table->unsignedSmallInteger('country_id')->nullable()->index();
                $table->foreign('country_id')->references('id')->on('central.countries');
                $table->string('checksum')->index()->nullable();
                $table->boolean('historic')->index()->default(false);
                $table->unsignedSmallInteger('usage')->default(0);
                $table->timestampsTz();
            }
        );

        Schema::create('addressables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('address_id')->index();
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->morphs('addressable');
            $table->string('scope')->nullable()->index();
            $table->string('sub_scope')->nullable()->index();
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('addressables');
        Schema::dropIfExists('addresses');
    }
};
