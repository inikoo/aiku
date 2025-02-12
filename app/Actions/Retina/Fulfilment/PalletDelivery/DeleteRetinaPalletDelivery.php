<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\PalletDelivery\DeletePalletDelivery;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class DeleteRetinaPalletDelivery extends RetinaAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public Customer $customer;
    private bool $action = false;

    public function handle(PalletDelivery $palletDelivery, array $modelData = []): void
    {
        DeletePalletDelivery::run($palletDelivery, $modelData);
    }

    public function htmlResponse(): Response
    {
        return Inertia::location(route('retina.fulfilment.storage.pallet_deliveries.index'));
    }

    public function rules(): array
    {
        return [
            'delete_comment' => ['sometimes', 'nullable']
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->action) {
            return true;
        } elseif ($this->customer->id == $request->route()->parameter('palletDelivery')->fulfilmentCustomer->customer_id) {
            return true;
        }

        return false;
    }

    public function asController(Organisation $organisation, PalletDelivery $palletDelivery, ActionRequest $request): void
    {
        $this->fulfilmentCustomer = $palletDelivery->fulfilmentCustomer;
        $this->initialisation($request);

        $this->handle($palletDelivery, $this->validatedData);
    }

}
