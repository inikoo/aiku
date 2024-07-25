<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\PalletDelivery\Search\PalletDeliveryRecordSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class UpdatePalletDelivery extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public Customer $customer;
    private bool $action = false;

    public function handle(PalletDelivery $palletDelivery, array $modelData): PalletDelivery
    {
        $palletDelivery =$this->update($palletDelivery, $modelData);
        PalletDeliveryRecordSearch::dispatch($palletDelivery);
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

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
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
            'customer_notes'          => ['sometimes','nullable','string','max:4000'],
            'estimated_delivery_date' => ['sometimes', 'date'],
            ...$rules
        ];
    }

    public function fromRetina(PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($palletDelivery, $this->validatedData);
    }


    public function asController(Organisation $organisation, PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);
        return $this->handle($palletDelivery, $this->validatedData);
    }

    public function action(PalletDelivery $palletDelivery, $modelData): PalletDelivery
    {
        $this->action = true;
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $modelData);
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
    //         default => Inertia::location(route('retina.storage.pallet-deliveries.show', [
    //             'palletDelivery'         => $palletDelivery->slug
    //         ]))
    //     };
    // }

}
