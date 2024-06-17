<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreementClause;

use App\Actions\Fulfilment\RentalAgreement\Hydrators\RentalAgreementHydrateClauses;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Fulfilment\RentalAgreementClause\RentalAgreementClauseTypeEnum;
use App\Models\Catalogue\Asset;
use App\Models\Fulfilment\RentalAgreement;
use App\Models\Fulfilment\RentalAgreementClause;
use Lorisleiva\Actions\ActionRequest;

class StoreRentalAgreementClause extends OrgAction
{
    public function handle(RentalAgreement $rentalAgreement, array $modelData): RentalAgreementClause
    {

        data_set($modelData, 'organisation_id', $rentalAgreement->organisation_id);
        data_set($modelData, 'group_id', $rentalAgreement->group_id);
        data_set($modelData, 'fulfilment_id', $rentalAgreement->fulfilment_id);
        data_set($modelData, 'fulfilment_customer_id', $rentalAgreement->fulfilment_customer_id);

        $asset = Asset::find($modelData['asset_id']);
        data_set(
            $modelData,
            'type',
            match($asset->type) {
                AssetTypeEnum::PRODUCT=> RentalAgreementClauseTypeEnum::PRODUCT,
                AssetTypeEnum::SERVICE=> RentalAgreementClauseTypeEnum::SERVICE,
                default               => RentalAgreementClauseTypeEnum::RENTAL
            }
        );

        /** @var RentalAgreementClause $rentalAgreementClause */
        $rentalAgreementClause = $rentalAgreement->clauses()->create($modelData);
        RentalAgreementHydrateClauses::dispatch($rentalAgreement);

        return $rentalAgreementClause;
    }

    public function rules(): array
    {
        return [
            'asset_id'                 => ['required', 'exists:assets,id'],
            'percentage_off'           => ['required', 'numeric','min:0','max:100'],
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
