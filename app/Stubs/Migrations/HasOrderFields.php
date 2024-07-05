<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 18:39:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasOrderFields
{
    public function orderMoneyFields(Blueprint $table): Blueprint
    {
        $table->decimal('net', 16)->default(0);
        $table->decimal('group_net_amount', 16)->default(0);
        $table->decimal('org_net_amount', 16)->default(0);


        $table->decimal('tax_rate', 16)->default(0);


        //$table->unsignedSmallInteger('tax_band_id')->nullable()->index();
        //$table->foreign('tax_band_id')->references('id')->on('tax_bands');


        $table->decimal('group_exchange', 16, 4)->default(1);
        $table->decimal('org_exchange', 16, 4)->default(1);

        return $table;
    }
}
