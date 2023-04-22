<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 12:15:28 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('media_agent', function (Blueprint $table) {
            $table->unsignedInteger('agent_id')->index();
            $table->string('type')->index();
            $table->foreign('agent_id')->references('id')->on('agents');
            $table->unsignedBigInteger('media_id')->index();
            $table->foreign('media_id')->references('id')->on('group_media');
            $table->unique(['agent_id', 'media_id']);
            $table->string('owner_type')->index();
            $table->unsignedInteger('owner_id');
            $table->boolean('public')->default(false)->index();

            $table->timestampsTz();
            $table->index(['owner_type', 'owner_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('media_agent');
    }
};
