<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:04:31 Central European Summer, Benalmádena, Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\DeliveryNote;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\IndexShops;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Delivery\DeliveryNoteResource;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Marketing\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDeliveryNote extends InertiaAction
{
    use AsAction;
    use WithInertia;

    public function handle(DeliveryNote $deliveryNote): DeliveryNote
    {
        return $deliveryNote;
    }

    public function authorize(ActionRequest $request): bool
    {
        //
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function asController(DeliveryNote $deliveryNote): DeliveryNote
    {
        return $this->handle($deliveryNote);
    }

    public function inShop(Shop $shop, DeliveryNote $deliveryNote, Request $request): DeliveryNote
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($deliveryNote);
    }

    public function htmlResponse(DeliveryNote $deliveryNote): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/DeliveryNote',
            [
                'title'       => __('delivery_note'),
                'breadcrumbs' => $this->getBreadcrumbs($deliveryNote),
                'pageHead'    => [
                    'title' => $deliveryNote->number,


                ],
                'delivery_note' => new DeliveryNoteResource($deliveryNote),
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(DeliveryNote $deliveryNote): DeliveryNoteResource
    {
        return new DeliveryNoteResource($deliveryNote);
    }


    public function getBreadcrumbs(DeliveryNote $deliveryNote): array
    {
        //TODO Pending
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $deliveryNote->id,
                    'name'            => $deliveryNote->number,
                    'index'           => [
                        'route'   => 'shops.index',
                        'overlay' => __('Delivery Notes list')
                    ],
                    'modelLabel' => [
                        'label' => __('deliveryNote')
                    ],
                ],
            ]
        );
    }
}
