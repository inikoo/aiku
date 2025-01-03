<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Jan 2025 17:46:17 Malaysia Time, Kuala Lumpur, Malaysia
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

class ResetYearIntervals
{
    use AsAction;

    protected string $signature = 'intervals:reset-year';
    protected string $description = 'Reset year intervals';


    public function handle(): void
    {
        $this->resetYearlyGroups();
        $this->resetYearlyOrganisations();
        $this->resetYearlyShops();
    }


    protected function resetYearlyGroups(): void
    {
        foreach (Group::all() as $group) {
            GroupHydrateSales::dispatch(
                group: $group,
                intervals: [
                    DateIntervalEnum::YEAR_TO_DAY
                ],
                doPreviousPeriods: ['previous_years']
            );
        }
    }

    protected function resetYearlyOrganisations(): void
    {
        foreach (Organisation::whereNot('type', OrganisationTypeEnum::AGENT)->get() as $organisation) {
            OrganisationHydrateSales::dispatch(
                organisation: $organisation,
                intervals: [
                    DateIntervalEnum::YEAR_TO_DAY
                ],
                doPreviousPeriods: ['previous_years']
            );
        }
    }

    protected function resetYearlyShops(): void
    {
        foreach (Shop::all() as $shop) {
            ShopHydrateSales::dispatch(
                shop: $shop,
                intervals: [
                    DateIntervalEnum::YEAR_TO_DAY
                ],
                doPreviousPeriods: ['previous_years']
            );
        }
    }


}
