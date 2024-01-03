<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Jan 2024 23:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasGroupOrganisationRelationship
{
    public function groupOrgRelationship(Blueprint $table): Blueprint
    {

        $table->unsignedSmallInteger('group_id')->index();
        $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
        $table->unsignedSmallInteger('organisation_id');
        $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');

        return $table;
    }
}
