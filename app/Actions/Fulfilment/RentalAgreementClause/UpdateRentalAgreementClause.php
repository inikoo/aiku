<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreementClause;

use App\Actions\Fulfilment\RentalAgreement\Hydrators\RentalAgreementHydrateClauses;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Enums\Fulfilment\RentalAgreementClause\RentalAgreementCauseStateEnum;
use App\Models\Fulfilment\RentalAgreementClause;

class UpdateRentalAgreementClause extends OrgAction
{
    use WithActionUpdate;

    public function handle(RentalAgreementClause $rentalAgreementClause, array $modelData): RentalAgreementClause
    {

        $rentalAgreementClause->update(
            [
                'state'      => RentalAgreementCauseStateEnum::UPDATED
            ]
        );
        $rentalAgreementClause->delete();

        $updatedModel=StoreRentalAgreementClause::run(
            $rentalAgreementClause->rentalAgreement,
            [
            'asset_id'       => $rentalAgreementClause->asset_id,
            'percentage_off' => $modelData['percentage_off'],
            'state'          => match ($rentalAgreementClause->rentalAgreement->state) {
                RentalAgreementStateEnum::ACTIVE      => RentalAgreementCauseStateEnum::ACTIVE,
                default                               => RentalAgreementCauseStateEnum::DRAFT
            }
        ]
        );

        RentalAgreementHydrateClauses::dispatch($rentalAgreementClause->rentalAgreement);

        return $updatedModel;
    }

    public function rules(): array
    {
        return [
            'percentage_off'           => ['required', 'numeric','min:0','max:100'],
        ];
    }

    public function action(RentalAgreementClause $rentalAgreementClause, array $modelData): RentalAgreementClause
    {
        $this->initialisation($rentalAgreementClause->organisation, $modelData);

        return $this->handle($rentalAgreementClause, $this->validatedData);
    }


}
