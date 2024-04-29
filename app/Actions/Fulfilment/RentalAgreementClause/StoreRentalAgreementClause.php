<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreementClause;

use App\Actions\OrgAction;
use App\Models\Fulfilment\RentalAgreement;
use App\Models\Fulfilment\RentalAgreementClause;
use Lorisleiva\Actions\ActionRequest;

class StoreRentalAgreementClause extends OrgAction
{
    public function handle(RentalAgreement $rentalAgreement, array $modelData): RentalAgreementClause
    {
        /** @var \App\Models\Fulfilment\RentalAgreementClause $rentalAgreementClause */
        $rentalAgreementClause = $rentalAgreement->clauses()->create($modelData);

        return $rentalAgreementClause;
    }

    public function rules(): array
    {
        return [
            'product_id'             => ['required', 'exists:products,id'],
            'agreed_price'           => ['required', 'integer'],
        ];
    }

    public function action(RentalAgreement $rentalAgreement, array $modelData): RentalAgreementClause
    {
        $this->asAction       = true;
        $this->initialisationFromShop($rentalAgreement->fulfilment->shop, $modelData);

        return $this->handle($rentalAgreement, $this->validatedData);
    }

    public function asController(RentalAgreement $rentalAgreement, ActionRequest $request): RentalAgreementClause
    {
        $this->initialisationFromShop($rentalAgreement->fulfilment->shop, $request);

        return $this->handle($rentalAgreement, $this->validatedData);
    }
}
