<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Apr 2024 01:34:40 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\PalletDelivery\CalculatePalletDeliveryNet;
use App\Actions\OrgAction;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\Rental;

class SetClausesInPallet extends OrgAction
{
    public function handle(Pallet $pallet, array $modelData)
    {
        $rental                 = Rental::find($modelData['rental_id']);
        $rentalAgreementClauses = $pallet->fulfilmentCustomer->rentalAgreementClauses;
        $found                  = false;

        foreach ($rentalAgreementClauses as $clause) {
            if ($clause->asset_id === $rental->asset_id) {
                data_set($modelData, 'rental_agreement_clause_id', $clause->id);
                $found = true;
                break;
            }
        }

        if (!$found) {
            data_set($modelData, 'rental_agreement_clause_id', null);
        }

        $pallet->update($modelData);

        CalculatePalletDeliveryNet::run($pallet->palletDelivery);
    }
}
