<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class DeletePalletDelivery extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public Customer $customer;
    private bool $action = false;
    private FulfilmentCustomer $fulfilmentCustomer;

    public function handle(PalletDelivery $palletDelivery): void
    {
        if (in_array($palletDelivery->state, [PalletDeliveryStateEnum::IN_PROCESS, PalletDeliveryStateEnum::SUBMITTED])) {
            $palletDelivery->pallets()->delete();
            $palletDelivery->transactions()->delete();
            $palletDelivery->delete();
        }
    }

    public function htmlResponse(): Response
    {
        return Inertia::location(route('grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.index', [
            'organisation' => $this->organisation->slug,
            'fulfilment' => $this->fulfilment->slug,
            'fulfilmentCustomer' => $this->fulfilmentCustomer->slug
        ]));
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->action) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function asController(Organisation $organisation, PalletDelivery $palletDelivery, ActionRequest $request): void
    {
        $this->fulfilmentCustomer = $palletDelivery->fulfilmentCustomer;
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        $this->handle($palletDelivery);
    }

    public function action(PalletDelivery $palletDelivery, $modelData): void
    {
        $this->action = true;
        $this->fulfilmentCustomer = $palletDelivery->fulfilmentCustomer;
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $modelData);

        $this->handle($palletDelivery);
    }
}
