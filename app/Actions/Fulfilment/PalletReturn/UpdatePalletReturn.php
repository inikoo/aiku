<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Helpers\Address\Hydrators\AddressHydrateUsage;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithModelAddressActions;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Address;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class UpdatePalletReturn extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;
    use WithModelAddressActions;

    public Customer $customer;
    /**
     * @var true
     */
    private bool $action = false;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        if(Arr::exists($modelData, 'address')) {
            $addressData = Arr::get($modelData, 'address');
            $groupId     =$palletReturn->group_id;

            data_set($addressData, 'group_id', $groupId);

            $address = Address::create($addressData);
            data_set($modelData, 'delivery_address_id', $address->id);
            AddressHydrateUsage::dispatch($address);

            Arr::forget($modelData, 'address');
        }

        return $this->update($palletReturn, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->action) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        $rules = [];

        if(!request()->user() instanceof WebUser) {
            $rules = [
                'public_notes'   => ['sometimes','nullable','string','max:4000'],
                'internal_notes' => ['sometimes','nullable','string','max:4000'],
            ];
        }

        return [
            'customer_notes' => ['sometimes','nullable','string', 'max:5000'],
            'address'        => ['sometimes'],
            ...$rules
        ];
    }


    public function fromRetina(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($palletReturn, $this->validatedData);
    }


    public function asController(Organisation $organisation, PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);
        return $this->handle($palletReturn, $this->validatedData);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function action(Organisation $organisation, PalletReturn $palletReturn, $modelData): PalletReturn
    {
        $this->action = true;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $modelData);
        $this->setRawAttributes($modelData);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function htmlResponse(PalletReturn $palletReturn, ActionRequest $request): Response
    {
        $routeName = $request->route()->getName();

        return match ($routeName) {
            'grp.models.fulfilment-customer.pallet-delivery.store' => Inertia::location(route('grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.show', [
                'organisation'           => $palletReturn->organisation->slug,
                'fulfilment'             => $palletReturn->fulfilment->slug,
                'fulfilmentCustomer'     => $palletReturn->fulfilmentCustomer->slug,
                'palletReturn'           => $palletReturn->slug
            ])),
            default => Inertia::location(route('retina.storage.pallet-deliveries.show', [
                'palletReturn'         => $palletReturn->slug
            ]))
        };
    }

    public string $commandSignature = 'pallet-deliveries:update {pallet-delivery}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $palletReturn = PalletReturn::where('slug', $command->argument('pallet-return'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());
            return 1;
        }

        try {
            $this->initialisationFromFulfilment($palletReturn->fulfilment, []);
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $palletReturn = $this->handle($palletReturn, modelData: $this->validatedData);

        $command->info("Pallet return $palletReturn->reference updated successfully 🎉");

        return 0;
    }
}
