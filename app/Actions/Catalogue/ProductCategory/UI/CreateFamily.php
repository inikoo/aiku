<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\ProductCategory\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateFamily extends OrgAction
{
    use HasCatalogueAuthorisation;
    private Shop|ProductCategory $parent;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Organisation) {
            $this->canEdit = $request->user()->hasAnyPermission(
                [
                    'org-supervisor.'.$this->organisation->id,
                ]
            );

            return $request->user()->hasAnyPermission(
                [
                    'org-supervisor.'.$this->organisation->id,
                    'shops-view'.$this->organisation->id,
                ]
            );
        } else {
            $this->canEdit = $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
            return $request->user()->hasPermissionTo("products.{$this->shop->id}.view");
        }
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inOrganisation(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->initialisation($organisation, $request);

        return $request;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisationFromShop($shop, $request);

        return $request;
    }

    public function inDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, ActionRequest $request): Response
    {
        $this->parent = $department;
        $this->initialisationFromShop($shop, $request);
        return $this->handle(parent: $department, request: $request);
    }


    public function handle(Shop|ProductCategory $parent, ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('New Family'),
                'pageHead'    => [
                    'title'        => __('new family'),
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => class_basename($this->parent) == 'ProductCategory'
                                                 ? 'grp.org.shops.show.catalogue.departments.show.families.index'
                                            : (class_basename($this->parent) == 'Shop'
                                                ? 'grp.org.shops.show.catalogue.families.index'
                                                : ''),
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' =>
                        [
                            [
                                'title'  => __('family'),
                                'fields' => [
                                    'type' => [
                                        'type'     => 'select',
                                        'label'    => __('type'),
                                        'required' => true,
                                        'options'  => Options::forEnum(ProductCategoryTypeEnum::class),
                                        'value'    => ProductCategoryTypeEnum::FAMILY->value,
                                        'readonly' => true
                                    ],
                                    'code' => [
                                        'type'     => 'input',
                                        'label'    => __('code'),
                                        'required' => true
                                    ],
                                    'name' => [
                                        'type'     => 'input',
                                        'label'    => __('name'),
                                        'required' => true
                                    ],
                                    'description' => [
                                        'type'     => 'textEditor',
                                        'label'    => __('name'),
                                        'required' => true
                                    ],
                                ]
                            ]
                        ],
                    'route' => match ($request->route()->getName()) {
                        'shops.show.families.create' => [
                            'name'      => 'grp.models.shop.family.store',
                            'arguments' => $this->shop->id
                        ],
                        'grp.org.shops.show.catalogue.departments.show.families.create' => [
                            'name'       => 'grp.models.org.catalogue.departments.family.store',
                            'parameters' => [
                                'organisation'    => $this->organisation->id,
                                'shop'            => $this->shop->id,
                                'productCategory' => $this->parent->id
                            ]
                        ],
                        default => [
                            'name' => 'grp.models.family.store'
                        ]
                    }
                ]
            ]
        );
    }



    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexFamilies::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'         => 'creatingModel',
                    'creatingModel'=> [
                        'label'=> __('Creating family'),
                    ]
                ]
            ]
        );
    }
}
