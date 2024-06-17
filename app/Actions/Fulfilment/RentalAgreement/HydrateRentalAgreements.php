<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 19:28:01 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreement;

use App\Actions\Fulfilment\RentalAgreement\Hydrators\RentalAgreementHydrateClauses;
use App\Actions\Fulfilment\RentalAgreement\Hydrators\RentalAgreementHydrateSnapShots;
use App\Actions\HydrateModel;
use App\Models\Fulfilment\RentalAgreement;
use Illuminate\Support\Collection;

class HydrateRentalAgreements extends HydrateModel
{
    public string $commandSignature = 'hydrate:rental-agreements {organisations?*} {--s|slugs=}';


    public function handle(RentalAgreement $rentalAgreement): void
    {
        RentalAgreementHydrateClauses::run($rentalAgreement);
        RentalAgreementHydrateSnapShots::run($rentalAgreement);
    }

    protected function getModel(string $slug): RentalAgreement
    {
        return RentalAgreement::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return RentalAgreement::withTrashed()->get();
    }
}
