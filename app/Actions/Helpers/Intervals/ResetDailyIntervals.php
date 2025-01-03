<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Jan 2025 23:34:09 Malaysia Time, Kuala Lumpur, Malaysia
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

class ResetDailyIntervals
{
    use AsAction;

    protected string $signature = 'intervals:reset-day';
    protected string $description = 'Reset day intervals';


    public function handle(): void
    {
        $this->resetDailyGroups();
        $this->resetDailyOrganisations();
        $this->resetDailyShops();
    }


    protected function resetDailyGroups(): void
    {
        foreach (Group::all() as $group) {
            GroupHydrateSales::dispatch(
                group: $group,
                intervals: [
                    DateIntervalEnum::YESTERDAY,
                    DateIntervalEnum::TODAY
                ],
                doPreviousPeriods: []
            );
        }
    }

    protected function resetDailyOrganisations(): void
    {
        foreach (Organisation::whereNot('type', OrganisationTypeEnum::AGENT)->get() as $organisation) {
            OrganisationHydrateSales::dispatch(
                organisation: $organisation,
                intervals: [
                    DateIntervalEnum::YESTERDAY,
                    DateIntervalEnum::TODAY
                ],
                doPreviousPeriods: []
            );
        }
    }

    protected function resetDailyShops(): void
    {
        foreach (Shop::all() as $shop) {
            ShopHydrateSales::dispatch(
                shop: $shop,
                intervals: [
                    DateIntervalEnum::YESTERDAY,
                    DateIntervalEnum::TODAY
                ],
                doPreviousPeriods: []
            );
        }
    }


}
