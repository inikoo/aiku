<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\PalletDelivery;

use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Event;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use OwenIt\Auditing\Events\AuditCustom;
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
        if (in_array($palletDelivery->state, [PalletDeliveryStateEnum::IN_PROCESS, PalletDeliveryStateEnum::SUBMITTED])) {

            $palletDelivery->pallets()->delete();
            $palletDelivery->transactions()->delete();

            $this->update($palletDelivery, [
                'delete_comment' => Arr::get($modelData, 'delete_comment')
            ]);

            $fulfilmentCustomer = $this->fulfilmentCustomer;

            $fulfilmentCustomer->customer->auditEvent    = 'delete';
            $fulfilmentCustomer->customer->isCustomEvent = true;

            $fulfilmentCustomer->customer->auditCustomOld = [
                'delivery' => $palletDelivery->reference
            ];

            $fulfilmentCustomer->customer->auditCustomNew = [
                'delivery' => __("The delivery has been deleted due to: $palletDelivery->delete_comment.")
            ];

            Event::dispatch(AuditCustom::class, [$fulfilmentCustomer->customer]);

            $palletDelivery->delete();
        } else {
            abort(401);
        }
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
