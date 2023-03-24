<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 12:25:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('stored_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('code')->index();
            $table->boolean('status')->default(false)->comment('false for returned goods');
            $table->string('state')->index();


            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedInteger('location_id')->index();
            $table->foreign('location_id')->references('id')->on('locations');

            $table->string('notes');
            $table->boolean('return_requested');

            $table->timestampsTz();
            $table->dateTimeTz('received_at')->nullable();
            $table->dateTimeTz('stored_at')->nullable();
            $table->dateTimeTz('returned_at')->nullable();
            $table->jsonb('data');
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('stored_items');
    }
};
