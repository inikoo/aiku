<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:16:52 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create(
            'group_addresses',
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
                $table->foreign('country_id')->references('id')->on('public.countries');
                $table->string('checksum')->index()->nullable();
                $table->boolean('historic')->index()->default(false);
                $table->unsignedInteger('usage')->default(0);
                $table->timestampsTz();
            }
        );

        Schema::create('group_addressables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('group_address_id')->index();
            $table->foreign('group_address_id')->references('id')->on('group_addresses');
            $table->morphs('group_addressable');
            $table->string('scope')->nullable()->index();
            $table->string('sub_scope')->nullable()->index();
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('group_addressables');
        Schema::dropIfExists('group_addresses');
    }
};
