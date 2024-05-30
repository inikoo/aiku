<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 14 Aug 2023 12:10:29 Malaysia Time, Pantai Lembeng, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasAssets
{
    public function assets(Blueprint $table): Blueprint
    {

        $table->unsignedSmallInteger('country_id');
        $table->foreign('country_id')->references('id')->on('countries');
        $table->unsignedSmallInteger('language_id');
        $table->foreign('language_id')->references('id')->on('languages');
        $table->unsignedSmallInteger('timezone_id');
        $table->foreign('timezone_id')->references('id')->on('timezones');
        $table->unsignedSmallInteger('currency_id')->comment('customer accounting currency');
        $table->foreign('currency_id')->references('id')->on('currencies');
        $table->unsignedInteger('image_id')->nullable();

        return $table;
    }
}
