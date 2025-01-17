<?php

/*
 * author Arya Permana - Kirin
 * created on 16-01-2025-13h-19m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Storage\PalletDelivery;

use App\Actions\Catalogue\HasRentalAgreement;
use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydratePalletDeliveries;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\Notifications\SendPalletDeliveryNotification;
use App\Actions\Fulfilment\PalletDelivery\Search\PalletDeliveryRecordSearch;
use App\Actions\Fulfilment\WithDeliverableStoreProcessing;
use App\Actions\Helpers\TaxCategory\GetTaxCategory;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydratePalletDeliveries;
use App\Actions\RetinaAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydratePalletDeliveries;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePalletDeliveries;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreRetinaPalletDelivery extends RetinaAction
{
    use HasRentalAgreement;
    use WithDeliverableStoreProcessing;


    public Customer $customer;

    private bool $action = false;
    private FulfilmentCustomer $fulfilmentCustomer;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): PalletDelivery
    {
        if (!Arr::exists($modelData, 'tax_category_id')) {
            data_set(
                $modelData,
                'tax_category_id',
                GetTaxCategory::run(
                    country: $this->organisation->country,
                    taxNumber: $fulfilmentCustomer->customer->taxNumber,
                    billingAddress: $fulfilmentCustomer->customer->address,
                    deliveryAddress: $fulfilmentCustomer->customer->address,
                )->id
            );
        }

        data_set($modelData, 'currency_id', $fulfilmentCustomer->fulfilment->shop->currency_id, overwrite: false);

        $modelData = $this->processData($modelData, $fulfilmentCustomer, SerialReferenceModelEnum::PALLET_DELIVERY);

        /** @var PalletDelivery $palletDelivery */
        $palletDelivery = $fulfilmentCustomer->palletDeliveries()->create($modelData);
        $palletDelivery->stats()->create();
        $palletDelivery->refresh();
        PalletDeliveryRecordSearch::dispatch($palletDelivery);

        GroupHydratePalletDeliveries::dispatch($fulfilmentCustomer->group);
        OrganisationHydratePalletDeliveries::dispatch($fulfilmentCustomer->organisation);
        WarehouseHydratePalletDeliveries::dispatch($palletDelivery->warehouse);
        FulfilmentCustomerHydratePalletDeliveries::dispatch($fulfilmentCustomer);
        FulfilmentHydratePalletDeliveries::dispatch($fulfilmentCustomer->fulfilment);


        SendPalletDeliveryNotification::dispatch($palletDelivery);

        return $palletDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->action) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return false;
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($this->fulfilment->warehouses()->count() == 1) {
            /** @var Warehouse $warehouse */
            $warehouse = $this->fulfilment->warehouses()->first();
            $this->fill(['warehouse_id' => $warehouse->id]);
        }
    }


    public function rules(): array
    {
        $rules = [];

        if (!request()->user() instanceof WebUser) {
            $rules = [
                'public_notes'   => ['sometimes', 'nullable', 'string', 'max:4000'],
                'internal_notes' => ['sometimes', 'nullable', 'string', 'max:4000'],
            ];
        }

        return [
            'warehouse_id'   => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')
                    ->where('organisation_id', $this->organisation->id),
            ],
            'customer_notes' => ['sometimes', 'nullable', 'string'],
            ...$rules
        ];
    }

    public function asController(ActionRequest $request): PalletDelivery
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


    public function htmlResponse(PalletDelivery $palletDelivery, ActionRequest $request): Response
    {
        return Inertia::location(route('retina.fulfilment.storage.pallet-deliveries.show', [
            'palletDelivery' => $palletDelivery->slug
        ]));
    }


}
