<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 10:25:14 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasDropshippingStats
{
    public function dropshippingStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_customer_clients')->default(0);
        $table->unsignedInteger('number_current_customer_clients')->default(0);
        $table->unsignedInteger('number_portfolios')->default(0);
        $table->unsignedInteger('number_current_portfolios')->default(0);
        if ($table->getTable() != 'customer_stats') {
            $table->unsignedInteger('number_portfolios_platform_shopify')->default(0);
            $table->unsignedInteger('number_portfolios_platform_woocommerce')->default(0);
        }

        return $table;
    }
}
