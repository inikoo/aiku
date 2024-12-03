<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 03 Dec 2024 15:04:17 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasPicking
{
    use HasGroupOrganisationRelationship;

    protected function pickingHead(Blueprint $table): Blueprint
    {

        $table->id();
        $table = $this->groupOrgRelationship($table);
        $table->unsignedSmallInteger('shop_id')->index();
        $table->foreign('shop_id')->references('id')->on('shops');
        $table->unsignedInteger('delivery_note_id')->index();
        $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');
        $table->unsignedBigInteger('delivery_note_item_id')->index();
        $table->foreign('delivery_note_item_id')->references('id')->on('delivery_note_items');

        return $table;
    }
}
