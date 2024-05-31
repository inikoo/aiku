<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreement;

use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStatus;
use App\Actions\Fulfilment\RentalAgreementClause\StoreRentalAgreementClause;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\RentalAgreement;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

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


        $causes=Arr::get($modelData, 'rental', []);
        data_forget($modelData, 'rental');





        if (!Arr::get($modelData, 'state')) {
            data_set($modelData, 'state', RentalAgreementStateEnum::ACTIVE->value);
        }

        /** @var RentalAgreement $rentalAgreement */
        $rentalAgreement = $fulfilmentCustomer->rentalAgreement()->create($modelData);


        foreach ($causes as $causeData) {
            foreach ($causeData as $data) {
                StoreRentalAgreementClause::run($rentalAgreement, $data);
            }
        }





        FulfilmentCustomerHydrateStatus::run($fulfilmentCustomer);


        return $rentalAgreement;
    }

    public function rules(): array
    {
        return [
            'billing_cycle'                  => ['required', Rule::enum(RentalAgreementBillingCycleEnum::class)],
            'pallets_limit'                  => ['nullable', 'integer', 'min:1', 'max:10000'],
            'rental'                         => ['sometimes', 'array'],
            'rental.rentals.*.product_id'    => ['sometimes',
                                         Rule::exists('products', 'id')
                                             ->where('shop_id', $this->fulfilment->shop_id)
                ],
            'rental.rentals.*.agreed_price'   => ['sometimes', 'numeric', 'gt:0'],
            'rental.services.*.product_id'    => ['sometimes',
                                         Rule::exists('products', 'id')
                                             ->where('shop_id', $this->fulfilment->shop_id)
                ],
            'rental.services.*.agreed_price'        => ['sometimes', 'numeric', 'gt:0'],
            'rental.physical_goods.*.product_id'    => ['sometimes',
                                         Rule::exists('products', 'id')
                                             ->where('shop_id', $this->fulfilment->shop_id)
                ],
            'rental.physical_goods.*.agreed_price'  => ['sometimes', 'numeric', 'gt:0'],
            'state'                                 => ['sometimes', Rule::enum(RentalAgreementStateEnum::class)],
            'created_at'                            => ['sometimes', 'date']
        ];
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData): RentalAgreement
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): RentalAgreement
    {
        // dd($request->all());
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function htmlResponse(RentalAgreement $rentalAgreement): Response
    {
        return Inertia::location(route('grp.org.fulfilments.show.crm.customers.show', [
            'organisation'       => $rentalAgreement->organisation->slug,
            'fulfilment'         => $rentalAgreement->fulfilment->slug,
            'fulfilmentCustomer' => $rentalAgreement->fulfilmentCustomer->slug
        ]));
    }
}
