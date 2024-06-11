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
        Schema::create('outboxes', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('post_room_id')->nullable();
            $table->foreign('post_room_id')->references('id')->on('post_rooms');
            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('type')->index();
            $table->string('name');
            $table->string('state')->index()->default('in-process');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->index();
        });
    }


    public function down()
    {
        Schema::dropIfExists('outboxes');
    }
};
