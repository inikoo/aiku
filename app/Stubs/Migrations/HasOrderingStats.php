<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 13:02:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteTypeEnum;
use App\Enums\Ordering\Order\OrderHandingTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Order\OrderStatusEnum;
use App\Enums\Ordering\Purge\PurgeStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasOrderingStats
{
    public function orderingStatsFields(Blueprint $table): Blueprint
    {

        $table = $this->ordersStatsFields($table);
        $table = $this->invoicesStatsFields($table);
        $table = $this->invoicedCustomersStatsFields($table);
        $table = $this->deliveryNotesStatsFields($table);

        if (!in_array($table->getTable(), ['customer_stats', 'customer_client_id', 'platform_stats'])) {
            $table = $this->purgeStatsFields($table);
        }

        return $table;
    }


    public function ordersStatsFields(Blueprint $table): Blueprint
    {
        $table->dateTimeTz('last_order_created_at')->nullable();
        $table->dateTimeTz('last_order_submitted_at')->nullable();
        $table->dateTimeTz('last_order_dispatched_at')->nullable();
        $table->unsignedInteger('number_orders')->default(0);

        foreach (OrderStateEnum::cases() as $case) {
            $table->unsignedInteger('number_orders_state_'.$case->snake())->default(0);
        }

        foreach (OrderStatusEnum::cases() as $case) {
            $table->unsignedInteger('number_orders_status_'.$case->snake())->default(0);
        }

        foreach (OrderHandingTypeEnum::cases() as $case) {
            $table->unsignedInteger('number_orders_handing_type_'.$case->snake())->default(0);
        }

        return $table;
    }

    public function invoicesStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_invoices')->default(0);
        $table->unsignedInteger('number_invoices_type_invoice')->default(0);
        $table->unsignedInteger('number_invoices_type_refund')->default(0);
        $table->dateTimeTz('last_invoiced_at')->nullable();

        return $table;
    }


    public function invoicedCustomersStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_invoiced_customers')->default(0);


        return $table;
    }


    public function deliveryNotesStatsFields(Blueprint $table): Blueprint
    {
        $table->dateTimeTz('last_delivery_note_created_at')->nullable();
        $table->dateTimeTz('last_delivery_note_dispatched_at')->nullable();

        $table->dateTimeTz('last_delivery_note_type_order_created_at')->nullable();
        $table->dateTimeTz('last_delivery_note_type_order_dispatched_at')->nullable();

        $table->dateTimeTz('last_delivery_note_type_replacement_created_at')->nullable();
        $table->dateTimeTz('last_delivery_note_type_replacement_dispatched_at')->nullable();


        $table->unsignedInteger('number_delivery_notes')->default(0);

        foreach (DeliveryNoteTypeEnum::cases() as $case) {
            $table->unsignedInteger('number_delivery_notes_type_'.$case->snake())->default(0);
        }

        foreach (DeliveryNoteStateEnum::cases() as $case) {
            $table->unsignedInteger('number_delivery_notes_state_'.$case->snake())->default(0);
        }

        foreach (DeliveryNoteStateEnum::cases() as $case) {
            if ($case->value != 'cancelled') {
                $table->unsignedInteger('number_delivery_notes_cancelled_at_state_'.$case->snake())->default(0);
            }
        }

        $table->unsignedInteger('number_delivery_notes_state_with_out_of_stock')->default(0);


        return $table;
    }

    public function purgeStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_purges')->default(0);

        foreach (PurgeStateEnum::cases() as $case) {
            $table->unsignedInteger('number_purges_state_'.$case->snake())->default(0);
        }

        foreach (PurgeTypeEnum::cases() as $case) {
            $table->unsignedInteger('number_purges_type_'.$case->snake())->default(0);
        }

        return $table;
    }


}
