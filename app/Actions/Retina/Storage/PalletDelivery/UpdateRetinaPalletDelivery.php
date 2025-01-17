<?php

/*
 * author Arya Permana - Kirin
 * created on 16-01-2025-13h-39m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Storage\PalletDelivery;

use App\Actions\Fulfilment\PalletDelivery\Search\PalletDeliveryRecordSearch;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class UpdateRetinaPalletDelivery extends RetinaAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    private bool $action = false;

    public function handle(PalletDelivery $palletDelivery, array $modelData): PalletDelivery
    {
        /** @var PalletDelivery $palletDelivery */
        $palletDelivery = $this->update($palletDelivery, $modelData);

        if ($palletDelivery->wasChanged('state')) {
            UpdateRetinaPalletDeliveryTimeline::run($palletDelivery, [
                'state' => $palletDelivery->state
            ]);
        }

        PalletDeliveryRecordSearch::dispatch($palletDelivery);

        return $palletDelivery;
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
            'customer_notes'            => ['sometimes', 'nullable', 'string', 'max:4000'],
            'estimated_delivery_date'   => ['sometimes', 'date'],
            'current_recurring_bill_id' => [
                'sometimes',
                'nullable',
                Rule::exists('recurring_bills', 'id')->where('fulfilment_id', $this->fulfilment->id)
            ],
            ...$rules
        ];
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request);

        return $this->handle($palletDelivery, $this->validatedData);
    }


    public function action(PalletDelivery $palletDelivery, $modelData): PalletDelivery
    {
        $this->action = true;
        $this->initialisationFulfilmentActions($palletDelivery->fulfilmentCustomer, $modelData);

        return $this->handle($palletDelivery, $this->validatedData);
    }

    // public function htmlResponse(PalletDelivery $palletDelivery, ActionRequest $request): Response
    // {
    //     $routeName = $request->route()->getName();

    //     return match ($routeName) {
    //         'grp.models.pallet-delivery.update' => Inertia::location(route('grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.show', [
    //             'organisation'           => $palletDelivery->organisation->slug,
    //             'fulfilment'             => $palletDelivery->fulfilment->slug,
    //             'fulfilmentCustomer'     => $palletDelivery->fulfilmentCustomer->slug,
    //             'palletDelivery'         => $palletDelivery->slug
    //         ])),
    //         default => Inertia::location(route('retina.fulfilment.storage.pallet-deliveries.show', [
    //             'palletDelivery'         => $palletDelivery->slug
    //         ]))
    //     };
    // }

}
