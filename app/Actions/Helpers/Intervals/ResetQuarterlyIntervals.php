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

class ResetQuarterlyIntervals
{
    use AsAction;

    protected string $signature = 'intervals:reset-quarter';
    protected string $description = 'Reset quarter intervals';


    public function handle(): void
    {
        $this->resetQuarterlyGroups();
        $this->resetQuarterlyOrganisations();
        $this->resetQuarterlyShops();
    }


    protected function resetQuarterlyGroups(): void
    {
        foreach (Group::all() as $group) {
            GroupHydrateSales::dispatch(
                group: $group,
                intervals: [
                    DateIntervalEnum::QUARTER_TO_DAY
                ],
                doPreviousPeriods: ['previous_quarters']
            );
        }
    }

    protected function resetQuarterlyOrganisations(): void
    {
        foreach (Organisation::whereNot('type', OrganisationTypeEnum::AGENT)->get() as $organisation) {
            OrganisationHydrateSales::dispatch(
                organisation: $organisation,
                intervals: [
                    DateIntervalEnum::QUARTER_TO_DAY
                ],
                doPreviousPeriods: ['previous_quarters']
            );
        }
    }

    protected function resetQuarterlyShops(): void
    {
        foreach (Shop::all() as $shop) {
            ShopHydrateSales::dispatch(
                shop: $shop,
                intervals: [
                    DateIntervalEnum::QUARTER_TO_DAY
                ],
                doPreviousPeriods: ['previous_quarters']
            );
        }
    }


}
