<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 23:29:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasLocationsStats
{
    public function locationsStats(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_locations')->default(0);
        $table->unsignedSmallInteger('number_locations_status_operational')->default(0);
        $table->unsignedSmallInteger('number_locations_status_broken')->default(0);
        $table->unsignedSmallInteger('number_empty_locations')->default(0);
        $table->unsignedSmallInteger('number_locations_no_stock_slots')->default(0);

        $table->unsignedSmallInteger('number_locations_allow_stocks')->default(0);
        $table->unsignedSmallInteger('number_locations_allow_fulfilment')->default(0);
        $table->unsignedSmallInteger('number_locations_allow_dropshipping')->default(0);


        $table->decimal('stock_value', 16)->default(0);

        return $table;
    }
}
