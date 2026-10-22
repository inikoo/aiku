<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Reports\BillingRegionTaxCategory;

use App\Models\Helpers\Country;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;

trait WithBillingRegion
{
    /**
     * Same regions as Aurora's report: a UK organisation splits GB+IM, EU and outside the EU, an EU organisation
     * its own country, the rest of the EU and outside, any other its own country and export.
     *
     * @return array{0: string, 1: array<int, string>}
     */
    protected function billingRegionSql(Organisation $organisation): array
    {
        $homeCode    = $organisation->country->code;
        $euCodes     = Country::getCountryCodesInEU();
        $euIn        = implode(',', array_fill(0, count($euCodes), '?'));
        $unknownCase = "case when billing_countries.code is null then 'Unknown'";

        if ($homeCode === 'GB') {
            return ["$unknownCase when billing_countries.code in ('GB','IM') then 'GBIM' when billing_countries.code in ($euIn) then 'EU' else 'NOEU' end", $euCodes];
        }

        if (Country::isInEU($homeCode)) {
            return ["$unknownCase when billing_countries.code = ? then ? when billing_countries.code in ($euIn) then 'EU' else 'NOEU' end", [$homeCode, $homeCode, ...$euCodes]];
        }

        return ["$unknownCase when billing_countries.code = ? then ? else 'Export' end", [$homeCode, $homeCode]];
    }

    protected function joinBillingCountry(QueryBuilder $query): void
    {
        $query->leftJoin('countries as billing_countries', 'billing_countries.id', '=', 'invoices.billing_country_id');
    }

    /**
     * @return array<string, string>
     */
    protected function billingRegionLabels(Organisation $organisation): array
    {
        return [
            'GBIM'    => 'GB+IM',
            'EU'      => __('European Union'),
            'NOEU'    => __('Outside European Union'),
            'Export'  => __('Export'),
            'Unknown' => __('Unknown'),
            $organisation->country->code => $organisation->country->name,
        ];
    }
}
