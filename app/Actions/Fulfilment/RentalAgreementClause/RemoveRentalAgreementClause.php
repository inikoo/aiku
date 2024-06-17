<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreementClause;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\RentalAgreementClause\RentalAgreementCauseStateEnum;
use App\Models\Fulfilment\RentalAgreementClause;

class RemoveRentalAgreementClause extends OrgAction
{
    use WithActionUpdate;

    public function handle(RentalAgreementClause $rentalAgreementClause): RentalAgreementClause
    {

        $rentalAgreementClause->update(
            [
                'state'      => RentalAgreementCauseStateEnum::REMOVED
            ]
        );
        $rentalAgreementClause->delete();

        return $rentalAgreementClause;
    }



    public function action(RentalAgreementClause $rentalAgreementClause, array $modelData): RentalAgreementClause
    {
        $this->initialisation($rentalAgreementClause->organisation, $modelData);
        return $this->handle($rentalAgreementClause);
    }


}
