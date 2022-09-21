<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 13:22:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create(
            'addresses',
            function (Blueprint $table) {
                $table->id();
                $table->boolean('immutable')->default(false)->index();
                $table->string('address_line_1',255)->nullable();
                $table->string('address_line_2',255)->nullable();
                $table->string('sorting_code')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('locality')->nullable();
                $table->string('dependant_locality')->nullable();
                $table->string('administrative_area')->nullable();
                $table->string('country_code', 2)->nullable()->index();
                $table->string('checksum')->index()->nullable();
                $table->unsignedSmallInteger('owner_id')->nullable()->index();
                $table->string('owner_type')->nullable()->index();
                $table->string('owner_scope')->nullable();
                $table->unsignedSmallInteger('country_id')->nullable()->index();
                $table->foreign('country_id')->references('id')->on('central.countries');
                $table->index(['checksum', 'owner_id', 'owner_type']);

                $table->timestampsTz();
            }
        );

        Schema::create('addressables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('address_id')->index();
            $table->unsignedBigInteger('addressable_id')->index();
            $table->string('addressable_type')->index();
            $table->string('scope')->nullable()->index();
            $table->string('status_info')->nullable();
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('addressables');
        Schema::dropIfExists('addresses');
    }
};
