<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreementSnapshot;

use App\Actions\OrgAction;
use App\Models\Fulfilment\RentalAgreement;
use App\Models\Fulfilment\RentalAgreementSnapshot;
use Lorisleiva\Actions\ActionRequest;

class StoreRentalAgreementSnapshot extends OrgAction
{
    public function handle(RentalAgreement $rentalAgreement, array $modelData): RentalAgreementSnapshot
    {
        /** @var RentalAgreementSnapshot $rentalAgreementSnapshot */
        $rentalAgreementSnapshot = $rentalAgreement->snapshot()->create([
            'data' => [
                'rental_agreement'         => $rentalAgreement->toArray(),
                'rental_agreement_clauses' => $rentalAgreement->clauses->toArray()
            ],
            'date' => now()
        ]);

        return $rentalAgreementSnapshot;
    }

    public function action(RentalAgreement $rentalAgreement, array $modelData): RentalAgreementSnapshot
    {
        $this->asAction       = true;
        $this->initialisationFromShop($rentalAgreement->fulfilment->shop, $modelData);

        return $this->handle($rentalAgreement, $this->validatedData);
    }

    public function asController(RentalAgreement $rentalAgreement, ActionRequest $request): RentalAgreementSnapshot
    {
        $this->initialisationFromShop($rentalAgreement->fulfilment->shop, $request);

        return $this->handle($rentalAgreement, $this->validatedData);
    }
}
