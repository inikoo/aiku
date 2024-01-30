<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
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

    public Customer $customer;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): PalletDelivery
    {
        data_set($modelData, 'group_id', $fulfilmentCustomer->group_id);
        data_set($modelData, 'organisation_id', $fulfilmentCustomer->organisation_id);

        data_set($modelData, 'ulid', Str::ulid());

        if (!Arr::get($modelData, 'reference')) {
            // data_set($modelData, 'reference', GetSerialReference::run(container: $fulfilmentCustomer->customer->shop, modelType: SerialReferenceModelEnum::PALLET_DELIVERY));
            data_set($modelData, 'reference', now()->timestamp);
        }

        /** @var PalletDelivery $palletDelivery */
        $palletDelivery = $fulfilmentCustomer->customer->palletDeliveries()->create($modelData);


        return $palletDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, Customer $customer, ActionRequest $request): PalletDelivery
    {
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($customer->fulfilmentCustomer, $this->validatedData);
    }

    public function jsonResponse(PalletDelivery $palletDelivery): array
    {
        return [
            'route' => [
                'name'       => 'grp.org.fulfilments.show.pallets.delivery.show',
                'parameters' => request()->route()->originalParameters() + ['palletDelivery' => $palletDelivery->reference]
            ]
        ];
    }

    public function htmlResponse(PalletDelivery $palletDelivery, ActionRequest $request): Response
    {
        return Inertia::location(route('grp.org.fulfilments.show.pallets.delivery.show', $request->route()->originalParameters() + ['palletDelivery' => $palletDelivery->reference]));
    }
}
