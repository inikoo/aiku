<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 23:29:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasSalesStats
{
    public function salesStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_orders')->default(0);

        foreach (OrderStateEnum::cases() as $case) {
            $table->unsignedInteger('number_orders_state_' . $case->snake())->default(0);
        }

        $table->unsignedInteger('number_invoices')->default(0);
        $table->unsignedInteger('number_invoices_type_invoice')->default(0);
        $table->unsignedInteger('number_invoices_type_refund')->default(0);


        $table->unsignedInteger('number_deliveries')->default(0);
        $table->unsignedInteger('number_deliveries_type_order')->default(0);
        $table->unsignedInteger('number_deliveries_type_replacement')->default(0);


        foreach (DeliveryNoteStateEnum::cases() as $deliveryState) {
            $table->unsignedInteger('number_deliveries_state_'.$deliveryState->snake())->default(0);
        }

        foreach (DeliveryNoteStateEnum::cases() as $deliveryState) {
            if ($deliveryState->value != 'cancelled') {
                $table->unsignedInteger('number_deliveries_cancelled_at_state_'.$deliveryState->snake())->default(0);
            }
        }

        $table->unsignedSmallInteger('currency_id')->nullable();
        $table->foreign('currency_id')->references('id')->on('currencies');

        return $table;
    }
}
