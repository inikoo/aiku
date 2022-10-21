<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 09:32:47 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Marketing\Family\StoreFamily;
use App\Actions\Marketing\Family\UpdateFamily;
use App\Models\Marketing\Family;
use App\Services\Tenant\SourceTenantService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class FetchFamilies
{
    use AsAction;


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Family
    {
        if ($familyData = $tenantSource->fetchFamily($tenantSourceId)) {
            if ($family = Family::where('source_id', $familyData['family']['source_id'])
                ->first()) {
                $family = UpdateFamily::run(
                    family:    $family,
                    modelData: $familyData['family'],
                );
            } else {
                $family = StoreFamily::run(
                    parent:    $familyData['parent'],
                    modelData: $familyData['family']
                );
            }

            return $family;
        }


        return null;
    }


}
