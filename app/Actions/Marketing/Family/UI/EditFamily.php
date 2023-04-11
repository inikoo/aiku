<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:09:31 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Family\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Marketing\FamilyResource;
use App\Models\Marketing\Family;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

class EditFamily extends InertiaAction
{
    use HasUIFamily;
    public function handle(Family $family): Family
    {
        return $family;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.products.edit');
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function inTenant(Family $family, ActionRequest $request): Family
    {
        $this->initialisation($request);

        return $this->handle($family);
    }

    public function inShop(Shop $shop, Family $family, ActionRequest $request): Family
    {
        $this->initialisation($request);

        return $this->handle($family);
    }

    public function inShopInDepartment(Shop $shop, Family $family, ActionRequest $request): Family
    {
        $this->initialisation($request);

        return $this->handle($family);
    }

    public function htmlResponse(Family $family): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('family'),
                'breadcrumbs' => $this->getBreadcrumbs($family),
                'pageHead'    => [
                    'title'     => $family->code,
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $family->code
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $family->name
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.family.update',
                            'parameters'=> $family->slug

                        ],
                    ]
                ]
            ]
        );
    }

    #[Pure] public function jsonResponse(Family $family): FamilyResource
    {
        return new FamilyResource($family);
    }
}
