<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Billable\UI;

use App\Actions\InertiaAction;
use App\Models\Catalogue\Billable;
use App\Models\Catalogue\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveProduct extends InertiaAction
{
    public function handle(Billable $product): Billable
    {
        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function inOrganisation(Billable $product, ActionRequest $request): Billable
    {
        $this->initialisation($request);

        return $this->handle($product);
    }



    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, Billable $product, ActionRequest $request): Billable
    {
        $this->initialisation($request);

        return $this->handle($product);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Billable'),
            'text'        => __("This action will delete this Billable and all it's dependent"),
            'route'       => $route
        ];
    }

    public function htmlResponse(Billable $product, ActionRequest $request): Response
    {

        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete product'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-cube'],
                            'title' => __('product')
                        ],
                    'title'  => $product->slug,
                    'actions'=> [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],
                'data'     => $this->getAction(
                    route:
                    match ($request->route()->getName()) {
                        'shops.products.remove' => [
                            'name'       => 'grp.models.product.delete',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'shops.show.products.remove' => [
                            'name'       => 'grp.models.shop.product.delete',
                            'parameters' => $request->route()->originalParameters()
                        ]
                    }
                )




            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowProduct::make()->getBreadcrumbs(
            routeName: preg_replace('/remove$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('deleting').')'
        );
    }
}
