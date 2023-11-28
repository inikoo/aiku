<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Nov 2023 20:45:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\HumanResources\Employee\EmployeeTypeEnum;
use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;

use App\Enums\Miscellaneous\GenderEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasHumanResourcesStats
{
    public function humanResourcesStats(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_employees')->default(0);
        foreach (EmployeeStateEnum::cases() as $case) {
            $table->unsignedSmallInteger('number_employees_state_'.$case->snake())->default(0);
        }
        foreach (EmployeeTypeEnum::cases() as $case) {
            $table->unsignedSmallInteger('number_employees_type_'.$case->snake())->default(0);
        }

        foreach (GenderEnum::cases() as $case) {
            $table->unsignedSmallInteger('number_employees_gender_'.$case->snake())->default(0);
        }


        $table->unsignedSmallInteger('number_workplaces')->default(0);
        foreach (WorkplaceTypeEnum::cases() as $case) {
            $table->unsignedSmallInteger('number_workplaces_type_'.$case->snake())->default(0);
        }

        $table->unsignedSmallInteger('number_clocking_machines')->default(0);
        $table->unsignedSmallInteger('number_clockings')->default(0);

        $table->timestampsTz();

        return $table;
    }
}
