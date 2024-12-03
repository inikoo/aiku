<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 03 Dec 2024 12:28:09 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasOrderHandlingFields
{
    public function orderHandlingFields(Blueprint $table): Blueprint
    {
        $orderStates = ['created', 'submitted', 'submitted_paid', 'submitted_not_paid', 'in_warehouse', 'handling_blocked', 'handling', 'packed', 'finalised'];
        $orderCompletedFields = ['packed_today', 'finalised_today', 'dispatched_today'];


        foreach ($orderStates as $state) {
            $table->unsignedInteger('number_orders_state_'.$state)->default(0);
            $table->dateTimeTz('latest_'.$state.'_order')->nullable()->comment('submitted_at, created_at for state=creating');
            $table->dateTimeTz('oldest_'.$state.'_order')->nullable()->comment('submitted_at, created_at for state=creating');
            $table->dateTimeTz('average_start_date_orders_state_'.$state)->nullable()->comment('based on submitted_at, created_at for state=creating');
            $table->dateTimeTz('average_date_for_orders_in_state_'.$state)->nullable();

            if ($table->getTable() == 'shop_order_handling_stats') {
                $table->decimal('orders_state_'.$state.'_amount', 16)->default(0);
            }
            if ($table->getTable() == 'shop_order_handling_stats' or $table->getTable() == 'organisation_order_handling_stats') {
                $table->decimal('orders_state_'.$state.'_amount_org_currency', 16)->default(0);
            }

            $table->decimal('orders_state_'.$state.'_amount_grp_currency', 16)->default(0);
        }

        foreach ($orderCompletedFields as $state) {
            $table->unsignedInteger('number_orders_'.$state)->default(0);
            if ($table->getTable() == 'shop_order_handling_stats') {
                $table->decimal('orders_'.$state.'_amount', 16)->default(0);
            }
            if ($table->getTable() == 'shop_order_handling_stats' or $table->getTable() == 'organisation_order_handling_stats') {
                $table->decimal('orders_'.$state.'_amount_org_currency', 16)->default(0);
            }
            $table->decimal('orders_'.$state.'_amount_grp_currency', 16)->default(0);
        }

        $deliveryNoteStates = ['queued', 'handling','handling_blocked', 'picking','picking_and_packing','packing', 'picking_blocked','picking_and_packing_blocked','packing_blocked',  'packed', 'finalised'];
        $deliveryNoteCompletedFields = ['picked_today','packed_today', 'dispatched_today'];


        foreach ($deliveryNoteStates as $state) {
            $table->unsignedInteger('number_delivery_notes_state_'.$state)->default(0);
            $table->dateTimeTz('latest_'.$state.'_delivery_note')->nullable();
            $table->dateTimeTz('oldest_'.$state.'_delivery_note')->nullable();
            $table->dateTimeTz('average_start_date_delivery_notes_state_'.$state)->nullable();
            $table->dateTimeTz('average_date_for_delivery_notes_in_state_'.$state)->nullable();


            $table->decimal('weight_delivery_notes_state_'.$state, 16)->default(0);
            $table->unsignedInteger('number_items_delivery_notes_state_'.$state)->default(0);
        }

        foreach ($deliveryNoteCompletedFields as $state) {
            $table->unsignedInteger('number_delivery_notes_'.$state)->default(0);
            $table->decimal('weight_delivery_notes_'.$state, 16)->default(0);
            $table->unsignedInteger('number_items_delivery_notes_'.$state)->default(0);
        }

        $pickingNoteStates = ['queued', 'picking','picking_blocked'];

        foreach ($pickingNoteStates as $state) {
            $table->unsignedInteger('number_pickings_state_'.$state)->default(0);
        }

        $pickingNoteStates = [ 'done_today'];

        foreach ($pickingNoteStates as $state) {
            $table->unsignedInteger('number_pickings_'.$state)->default(0);
        }

        $packingNoteStates = ['todo', 'done_today'];

        foreach ($packingNoteStates as $state) {
            $table->unsignedInteger('number_packings_'.$state)->default(0);
        }



        return $table;
    }

}
