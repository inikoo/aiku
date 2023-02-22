<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 22 Feb 2023 11:05:40 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Family;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\IndexShops;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Http\Resources\Marketing\FamilyResource;
use App\Models\Marketing\Department;
use App\Models\Marketing\Family;
use App\Models\Marketing\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


class ShowFamily extends InertiaAction
{
    use AsAction;
    use WithInertia;

    public function handle(Family $family): Family
    {
        return $family;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function asController(Family $family): Family
    {
        return $this->handle($family);
    }

    public function inShop(Shop $shop, Family $family, Request $request): Family
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($family);
    }

    public function htmlResponse(Family $family): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Family',
            [
                'title' => __('family'),
                'breadcrumbs' => $this->getBreadcrumbs($family),
                'pageHead' => [
                    'title' => $family->code,


                ],
                'family' => new FamilyResource($family),
                'treeMaps' => [
                    [
                        [
                            'name' => __('products'),
                            'icon' => ['fal', 'fa-cube'],
                            'href' => ['shops.show.products.index', $family->slug],
                            'index' => [
                                'number' => $family->stats->number_products
                            ]
                        ],
                    ],
                ]
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Family $family): FamilyResource
    {
        return new FamilyResource($family);
    }


    public function getBreadcrumbs(Family $family): array
    {
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route' => 'shops.show',
                    'routeParameters' => $family->id,
                    'name' => $family->code,
                    'index' => [
                        'route' => 'shops.index',
                        'overlay' => __('Families list')
                    ],
                    'modelLabel' => [
                        'label' => __('family')
                    ],
                ],
            ]
        );
    }

}
