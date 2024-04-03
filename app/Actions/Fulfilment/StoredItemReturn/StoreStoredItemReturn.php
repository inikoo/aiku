<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 14 Feb 2024 16:17:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemReturn;

use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemReturn;
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

class StoreStoredItemReturn extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;
    /**
     * @var true
     */
    private bool $action = false;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): StoredItemReturn
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
                    modelType: SerialReferenceModelEnum::STORED_ITEM_RETURN
                )
            );
        }

        HydrateFulfilmentCustomer::dispatch($fulfilmentCustomer);

        /** @var \App\Models\Fulfilment\StoredItemReturn $storedItemReturn */
        $storedItemReturn = $fulfilmentCustomer->storedItemReturns()->create($modelData);

        return $storedItemReturn;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->action) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            // TODO: Raul please do the permission for the web user
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }


    public function prepareForValidation(ActionRequest $request): void
    {
        if($this->fulfilment->warehouses()->count()==1) {
            $this->fill(['warehouse_id' =>$this->fulfilment->warehouses()->first()->id]);
        }
    }


    public function rules(): array
    {
        return [
            'warehouse_id'=> ['required','integer','exists:warehouses,id'],
        ];
    }


    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): StoredItemReturn
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


    public function fromRetina(ActionRequest $request): StoredItemReturn
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request->get('website')->organisation, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function action(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, $modelData): StoredItemReturn
    {
        $this->action = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);
        $this->setRawAttributes($modelData);

        return $this->handle($fulfilmentCustomer, $this->validateAttributes());
    }

    public function jsonResponse(StoredItemReturn $palletReturn): array
    {
        return [
            'route' => [
                'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-returns.show',
                'parameters' => [
                    'organisation'           => $palletReturn->organisation->slug,
                    'fulfilment'             => $palletReturn->fulfilment->slug,
                    'fulfilmentCustomer'     => $palletReturn->fulfilmentCustomer->slug,
                    'palletReturn'           => $palletReturn->reference
                ]
            ]
        ];
    }

    public function htmlResponse(StoredItemReturn $palletReturn, ActionRequest $request): Response
    {
        $routeName = $request->route()->getName();

        return match ($routeName) {
            'grp.models.fulfilment-customer.stored-item-return.store' => Inertia::location(route('grp.org.fulfilments.show.crm.customers.show.stored-item-returns.show', [
                'organisation'               => $palletReturn->organisation->slug,
                'fulfilment'                 => $palletReturn->fulfilment->slug,
                'fulfilmentCustomer'         => $palletReturn->fulfilmentCustomer->slug,
                'storedItemReturn'           => $palletReturn->reference
            ])),
            default => Inertia::location(route('retina.storage.stored-item-returns.show', [
                'storedItemReturn'         => $palletReturn->reference
            ]))
        };
    }

    public string $commandSignature = 'stored-item-returns:create {fulfilment-customer}';

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

        $palletReturn = $this->handle($fulfilmentCustomer, modelData: $this->validatedData);

        $command->info("Pallet delivery $palletReturn->reference created successfully 🎉");

        return 0;
    }


}
