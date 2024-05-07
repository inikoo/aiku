<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydratePalletDeliveries;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydrateUniversalSearch;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\Market\HasRentalAgreement;
use App\Actions\OrgAction;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class StorePalletDelivery extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use HasRentalAgreement;

    public Customer $customer;
    /**
     * @var true
     */
    private bool $action = false;
    /**
     * @var \App\Models\Fulfilment\FulfilmentCustomer
     */
    private FulfilmentCustomer $fulfilmentCustomer;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): PalletDelivery
    {
        data_set($modelData, 'group_id', $fulfilmentCustomer->group_id);
        data_set($modelData, 'organisation_id', $fulfilmentCustomer->organisation_id);
        data_set($modelData, 'fulfilment_id', $fulfilmentCustomer->fulfilment_id);
        data_set($modelData, 'in_process_at', now());

        data_set($modelData, 'ulid', Str::ulid());

        if (!Arr::get($modelData, 'reference')) {
            data_set(
                $modelData,
                'reference',
                GetSerialReference::run(
                    container: $fulfilmentCustomer,
                    modelType: SerialReferenceModelEnum::PALLET_DELIVERY
                )
            );

        }



        /** @var PalletDelivery $palletDelivery */
        $palletDelivery = $fulfilmentCustomer->palletDeliveries()->create($modelData);
        $palletDelivery->stats()->create();
        $palletDelivery->refresh();
        PalletDeliveryHydrateUniversalSearch::dispatch($palletDelivery);
        FulfilmentCustomerHydratePalletDeliveries::dispatch($fulfilmentCustomer);
        FulfilmentHydratePalletDeliveries::dispatch($fulfilmentCustomer->fulfilment);

        SendPalletDeliveryNotification::run($palletDelivery);

        return $palletDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->action) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        if($this->hasRentalAgreement($this->fulfilmentCustomer)) {
            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        }

        return false;
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if($this->fulfilment->warehouses()->count()==1) {
            $this->fill(['warehouse_id' =>$this->fulfilment->warehouses()->first()->id]);
        }
    }


    public function rules(): array
    {
        $rules = [];

        if(!request()->user() instanceof WebUser) {
            $rules = [
                'public_notes'  => ['sometimes','nullable','string','max:4000'],
                'internal_notes'=> ['sometimes','nullable','string','max:4000'],
            ];
        }

        return [
            'warehouse_id'  => ['required','integer','exists:warehouses,id'],
            'customer_notes'=> ['sometimes','nullable','string'],
            ...$rules
        ];
    }


    public function fromRetina(ActionRequest $request): PalletDelivery
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): PalletDelivery
    {
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);
        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, $modelData): PalletDelivery
    {
        $this->action = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);
        $this->setRawAttributes($modelData);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function jsonResponse(PalletDelivery $palletDelivery): array
    {
        return [
            'route' => [
                'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show',
                'parameters' => [
                    'organisation'           => $palletDelivery->organisation->slug,
                    'fulfilment'             => $palletDelivery->fulfilment->slug,
                    'fulfilmentCustomer'     => $palletDelivery->fulfilmentCustomer->slug,
                    'palletDelivery'         => $palletDelivery->reference
                ]
            ]
        ];
    }

    public function htmlResponse(PalletDelivery $palletDelivery, ActionRequest $request): Response
    {
        $routeName = $request->route()->getName();

        return match ($routeName) {
            'grp.models.fulfilment-customer.pallet-delivery.store' => Inertia::location(route('grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show', [
                'organisation'           => $palletDelivery->organisation->slug,
                'fulfilment'             => $palletDelivery->fulfilment->slug,
                'fulfilmentCustomer'     => $palletDelivery->fulfilmentCustomer->slug,
                'palletDelivery'         => $palletDelivery->slug
            ])),
            default => Inertia::location(route('retina.storage.pallet-deliveries.show', [
                'palletDelivery'         => $palletDelivery->slug
            ]))
        };
    }

    public string $commandSignature = 'pallet-deliveries:create {fulfilment-customer}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $fulfilmentCustomer = FulfilmentCustomer::where('slug', $command->argument('fulfilment-customer'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());
            return 1;
        }

        try {
            $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, []);
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $palletDelivery = $this->handle($fulfilmentCustomer, modelData: $this->validatedData);

        $command->info("Pallet delivery $palletDelivery->reference created successfully 🎉");

        return 0;
    }


}
