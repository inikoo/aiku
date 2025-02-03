<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 31-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Stubs\Migrations;

use App\Enums\Fulfilment\Space\SpaceStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasSpaceStats
{
    public function getSpaceFields(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_spaces')->default(0);

        foreach (SpaceStateEnum::cases() as $spaceState) {
            $table->unsignedInteger('number_spaces_state_'.$spaceState->snake())->default(0);
        }

        return $table;
    }

    public function dropSpaceFields(Blueprint $table): Blueprint
    {
        $table->dropColumn('number_spaces');

        foreach (SpaceStateEnum::cases() as $spaceState) {
            $table->dropColumn('number_spaces_state_'.$spaceState->snake());
        }

        return $table;
    }
}
