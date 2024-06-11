<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 10:25:14 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Catalogue\Product\ProductStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasDropshippingStats
{
    public function dropshippingStats(Blueprint $table): Blueprint
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
