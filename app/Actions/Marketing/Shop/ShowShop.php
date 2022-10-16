<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:04:31 Central European Summer Time, Benalmádena, Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Shop;

use App\Actions\UI\WithInertia;
use App\Http\Resources\Marketing\ShopResource;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


class ShowShop
{
    use AsAction;
    use WithInertia;

    public function handle(Shop $shop): Shop
    {
        return $shop;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.view");
    }

    public function asController(Shop $shop): Shop
    {
        return $this->handle($shop);
    }

    public function htmlResponse(Shop $shop): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Shop',
            [
                'title'=>__('shop'),
                'breadcrumbs' => $this->getBreadcrumbs($shop),
                'pageHead'=>[
                    'title'=>$shop->name,



                ],
                'shop'    => new ShopResource($shop)
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Shop $shop): ShopResource
    {
        return new ShopResource($shop);
    }


    public function getBreadcrumbs(Shop $shop): array
    {
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $shop->id,
                    'name'            => $shop->code,
                    'index'           => [
                        'route'   => 'shops.index',
                        'overlay' => __('Shops list')
                    ],
                    'modelLabel'      => [
                        'label' => __('shop')
                    ],
                ],
            ]
        );
    }

}
