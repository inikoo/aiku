<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 23:40:14 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\RentalAgreement;

use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Market\RentalAgreement;

class StoreRentalAgreement extends OrgAction
{
    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): RentalAgreement
    {

        data_set($modelData, 'organisation_id', $fulfilmentCustomer->organisation_id);
        data_set($modelData, 'group_id', $fulfilmentCustomer->group_id);
        data_set($modelData, 'fulfilment_id', $fulfilmentCustomer->fulfilment_id);

        data_set(
            $modelData,
            'reference',
            GetSerialReference::run(
                container: $fulfilmentCustomer->fulfilment,
                modelType: SerialReferenceModelEnum::RENTAL_AGREEMENT
            )
        );

        /** @var RentalAgreement $rentalAgreement */
        $rentalAgreement=$fulfilmentCustomer->rentalAgreement()->create($modelData);

        return $rentalAgreement;
    }

    public function rules(): array
    {
        return [
            'billing_cycle'=> ['sometimes','nullable','integer','min:1','max:100'],
            'pallets_limit'=> ['sometimes','nullable','integer','min:1','max:10000'],
        ];

    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData): RentalAgreement
    {
        $this->asAction       = true;

        $this->initialisationFromShop($fulfilmentCustomer->fulfilment->shop, $modelData);
        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }




}
