<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jul 2023 12:44:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Enums\Fulfilment\StoredItem\StoredItemStatusEnum;
use App\Enums\Fulfilment\StoredItem\StoredItemTypeEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasFulfilmentStats
{
    public function fulfilmentStats(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_stored_items')->default(0);

        foreach (StoredItemTypeEnum::cases() as $type) {
            $table->unsignedBigInteger("number_stored_items_type_{$type->snake()}")->default(0);
        }
        foreach (StoredItemStateEnum::cases() as $state) {
            $table->unsignedBigInteger("number_stored_items_state_{$state->snake()}")->default(0);
        }
        foreach (StoredItemStatusEnum::cases() as $status) {
            $table->unsignedBigInteger("number_stored_items_status_{$status->snake()}")->default(0);
        }



        return $table;
    }

    public function containerFulfilmentStats(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_customers_with_stored_items')->default(0);

        foreach (StoredItemStateEnum::cases() as $state) {
            $table->unsignedBigInteger("number_customers_with_stored_items_state_{$state->snake()}")->default(0);
        }
        foreach (StoredItemStatusEnum::cases() as $status) {
            $table->unsignedBigInteger("number_customers_with_stored_items_status_{$status->snake()}")->default(0);
        }

        return $table;
    }

}
