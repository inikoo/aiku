<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 12:17:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Manufacturing\Production\ProductionStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasManufactureStats
{
    public function productionsStats(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_productions')->default(0);
        foreach (ProductionStateEnum::cases() as $case) {
            $table->unsignedInteger('number_productions_state_'.$case->snake())->default(0);
        }
        return $table;
    }



}
