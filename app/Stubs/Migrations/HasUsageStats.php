<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 17:38:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasUsageStats
{
    public function usageStats(Blueprint $table): Blueprint
    {
        $table->timestampTz('first_used_at')->nullable();
        $table->timestampTz('last_used_at')->nullable();

        return $this->usageBaseStats($table);
    }

    public function usageBaseStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_customers')->default(0);
        $table->unsignedInteger('number_orders')->default(0);
        $table->unsignedInteger('number_invoices')->default(0);
        $table->unsignedInteger('number_delivery_notes')->default(0);
        $table->decimal('amount')->default(0);
        if (!in_array($table->getTable(), ['group_discounts_stats', 'organisation_discounts_stats'])) {
            $table->decimal('org_amount')->default(0);
        }

        if ($table->getTable() != 'group_discounts_stats') {
            $table->decimal('group_amount')->default(0);
        }

        return $table;
    }

}
