<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 23:29:40 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasAssetModel
{
    use HasGroupOrganisationRelationship;

    public function assetModelFields(Blueprint $table): Blueprint
    {

        $table->string('slug')->unique()->collation('und_ns');
        $table->string('code')->index()->collation('und_ns');
        $table->string('name', 255)->nullable();
        $table->text('description')->nullable()->fulltext();

        $table->decimal('price', 18)->nullable();
        $table->unsignedSmallInteger('units')->default(1);
        $table->string('unit');

        $table->jsonb('data');

        $table->unsignedSmallInteger('currency_id');
        $table->foreign('currency_id')->references('id')->on('currencies');
        $table->unsignedInteger('current_historic_asset_id')->index()->nullable();
        $table->foreign('current_historic_asset_id')->references('id')->on('historic_assets');
        return $table;
    }

    public function productFields(Blueprint $table): Blueprint
    {
        $table->increments('id');
        $table = $this->groupOrgRelationship($table);
        $table->unsignedSmallInteger('shop_id')->nullable();
        $table->foreign('shop_id')->references('id')->on('shops');
        $table->unsignedInteger('asset_id')->nullable();
        $table->foreign('asset_id')->references('id')->on('assets');
        $table->unsignedSmallInteger('family_id')->nullable();
        $table->unsignedSmallInteger('department_id')->nullable();
        return $table;
    }


}
