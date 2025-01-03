<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 04 Jan 2025 00:00:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Intervals;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateSales;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateSales;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateSales;
use App\Enums\DateIntervals\DateIntervalEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class ResetMonthlyIntervals
{
    use AsAction;

    protected string $signature = 'intervals:reset-month';
    protected string $description = 'Reset monthly intervals';


    public function handle(): void
    {
        $this->resetMonthlyGroups();
        $this->resetMonthlyOrganisations();


        $this->resetMonthlyShops();
    }


    protected function resetMonthlyGroups(): void
    {
        foreach (Group::all() as $group) {
            GroupHydrateSales::dispatch(
                group: $group,
                intervals: [
                    DateIntervalEnum::LAST_MONTH,
                    DateIntervalEnum::MONTH_TO_DAY
                ],
                doPreviousPeriods: []
            );
        }
    }

    protected function resetMonthlyOrganisations(): void
    {
        foreach (Organisation::whereNot('type', OrganisationTypeEnum::AGENT)->get() as $organisation) {
            OrganisationHydrateSales::dispatch(
                organisation: $organisation,
                intervals: [
                    DateIntervalEnum::LAST_MONTH,
                    DateIntervalEnum::MONTH_TO_DAY
                ],
                doPreviousPeriods: []
            );
        }
    }

    protected function resetMonthlyShops(): void
    {
        foreach (Shop::all() as $shop) {
            ShopHydrateSales::dispatch(
                shop: $shop,
                intervals: [
                    DateIntervalEnum::LAST_MONTH,
                    DateIntervalEnum::MONTH_TO_DAY
                ],
                doPreviousPeriods: []
            );
        }
    }


}
