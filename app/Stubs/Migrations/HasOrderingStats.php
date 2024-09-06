<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 13:02:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Catalogue\Charge\ChargeStateEnum;

use App\Enums\Ordering\Adjustment\AdjustmentTypeEnum;
use App\Enums\Ordering\ShippingZoneSchema\ShippingZoneSchemaTypeEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasOrderingStats
{
    public function billableFields(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_charges')->default(0);
        foreach (ChargeStateEnum::cases() as $case) {
            $table->unsignedInteger('number_charges_state_'.$case->snake())->default(0);
        }

        $table->unsignedInteger('number_shipping_zone_schemas')->default(0);
        foreach (ShippingZoneSchemaTypeEnum::cases() as $case) {
            $table->unsignedInteger('number_shipping_zone_schemas_type_'.$case->snake())->default(0);
        }

        $table->unsignedInteger('number_shipping_zones')->default(0);

        $table->unsignedInteger('number_adjustments')->default(0);
        foreach (AdjustmentTypeEnum::cases() as $case) {
            $table->unsignedInteger('number_adjustments_type_'.$case->snake())->default(0);
        }

        return $table;
    }


}
