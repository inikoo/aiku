<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\UI;

use App\Actions\InertiaAction;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveFamily extends InertiaAction
{
    public function handle(ProductCategory $family): ProductCategory
    {
        return $family;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function inOrganisation(ProductCategory $family, ActionRequest $request): ProductCategory
    {
        $this->initialisation($request);

        return $this->handle($family);
    }

    public function asController(ProductCategory $family, ActionRequest $request): ProductCategory
    {
        $this->initialisation($request);

        return $this->handle($family);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, ProductCategory $family, ActionRequest $request): ProductCategory
    {
        $this->initialisation($request);

        return $this->handle($family);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Family'),
            'text'        => __("This action will delete this Family and all it's dependent"),
            'route'       => $route
        ];
    }

    public function htmlResponse(ProductCategory $department, ActionRequest $request): Response
    {

        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete family'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-folder'],
                            'title' => __('family')
                        ],
                    'title'  => $department->slug,
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
                        'shops.families.remove' => [
                            'name'       => 'grp.models.family.delete',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'shops.show.families.remove' => [
                            'name'       => 'grp.models.shop.family.delete',
                            'parameters' => $request->route()->originalParameters()
                        ]
                    }
                )




            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowFamily::make()->getBreadcrumbs(
            routeName: preg_replace('/remove$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('deleting').')'
        );
    }
}
