<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 12:17:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Catalogue\Product\ProductStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasDropshippingStats
{
    public function stats(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_customer_clients')->default(0);
        $table->unsignedSmallInteger('number_current_customer_clients')->default(0);
        $table->unsignedSmallInteger('number_dropshipping_customer_portfolios')->default(0);
        $table->unsignedSmallInteger('number_current_dropshipping_customer_portfolios')->default(0);
        $table->unsignedSmallInteger('number_products')->default(0);
        $table->unsignedSmallInteger('number_current_products')->default(0);

        foreach (ProductStateEnum::cases() as $case) {
            $table->unsignedInteger('number_products_state_'.$case->snake())->default(0);
        }

        return $table;
    }
}
