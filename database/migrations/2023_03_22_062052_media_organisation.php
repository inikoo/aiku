<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:23:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('media_organisation', function (Blueprint $table) {
            $table->unsignedInteger('media_id')->index();
            $table->foreign('media_id')->references('id')->on('media');
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->timestampsTz();
            $table->unique(['media_id', 'organisation_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('media_organisation');
    }
};
