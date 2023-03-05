<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 11:27:06 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->enum('type', ['supplier', 'sub-supplier'])->index()->comment('sub-supplier: agents supplier');
            $table->unsignedMediumInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('agents');

            $table->boolean('status')->default(true)->index();
            $table->string('slug')->unique();
            $table->string('code')->index();
            $table->morphs('owner');
            $table->string('name');
            $table->string('company_name', 256)->nullable();
            $table->string('contact_name', 256)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('address_id')->nullable()->index();
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->jsonb('location');

            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('central.currencies');
            $table->jsonb('settings');
            $table->jsonb('shared_data');
            $table->jsonb('tenant_data');

            $table->timestampsTz();
            $table->softDeletesTz();
            $table->uuid('global_id')->index()->nullable();
            $table->unsignedBigInteger('source_id')->index()->nullable();
        });
    }


    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
};
