<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\DeletePalletReturn;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class DeleteRetinaPalletReturn extends RetinaAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public Customer $customer;
    private bool $action = false;

    public function handle(PalletReturn $palletReturn, array $modelData = []): void
    {
        DeletePalletReturn::run($palletReturn, $modelData);
    }

    public function htmlResponse(): Response
    {
        return Inertia::location(route('retina.fulfilment.storage.pallet_returns.index'));
    }

    public function rules()
    {
        return [
            'delete_comment' => ['sometimes', 'required']
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->action) {
            return true;
        } elseif ($this->customer->id == $request->route()->parameter('palletReturn')->fulfilmentCustomer->customer_id) {
            return true;
        }

        return false;
    }

    public function asController(Organisation $organisation, PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->fulfilmentCustomer = $palletReturn->fulfilmentCustomer;
        $this->initialisation($request);

        $this->handle($palletReturn, $this->validatedData);
    }

    public function action(PalletReturn $palletReturn, $modelData): void
    {
        $this->action = true;
        $this->fulfilmentCustomer = $palletReturn->fulfilmentCustomer;
        $this->initialisationFulfilmentActions($this->fulfilmentCustomer, $modelData);

        $this->handle($palletReturn, $this->validatedData);
    }
}
