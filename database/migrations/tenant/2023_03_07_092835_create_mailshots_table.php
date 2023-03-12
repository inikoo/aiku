<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('mailshots', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedSmallInteger('outbox_id')->nullable();
            $table->foreign('outbox_id')->references('id')->on('outboxes');

            $table->string('state')->index();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('mailshots');
    }
};
