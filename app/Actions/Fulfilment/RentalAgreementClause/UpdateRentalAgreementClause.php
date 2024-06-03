<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreementClause;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\RentalAgreementClause;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateRentalAgreementClause extends OrgAction
{
    use WithActionUpdate;

    public function handle(RentalAgreementClause $rentalAgreementClause, array $modelData): RentalAgreementClause
    {
        $modelData = Arr::only($modelData, 'agreed_price');
        /** @var \App\Models\Fulfilment\RentalAgreementClause $rentalAgreementClause */
        $rentalAgreementClause = $this->update($rentalAgreementClause, $modelData);

        return $rentalAgreementClause;
    }

    public function rules(): array
    {
        return [
            'asset_id'               => ['required', 'exists:assets,id'],
            'agreed_price'           => ['required', 'integer'],
        ];
    }

    public function action(RentalAgreementClause $rentalAgreementClause, array $modelData): RentalAgreementClause
    {
        $this->asAction       = true;
        $this->initialisationFromShop($rentalAgreementClause->rental->shop, $modelData);

        return $this->handle($rentalAgreementClause, $this->validatedData);
    }

    public function asController(RentalAgreementClause $rentalAgreementClause, ActionRequest $request): RentalAgreementClause
    {
        $this->initialisationFromShop($rentalAgreementClause->rental->shop, $request);

        return $this->handle($rentalAgreementClause, $this->validatedData);
    }
}
