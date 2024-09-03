<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 11:47:26 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shipping\UI;

use App\Actions\Catalogue\Collection\UI\ShowCollection;
use App\Actions\Catalogue\ProductCategory\UI\ShowDepartment;
use App\Actions\Catalogue\ProductCategory\UI\ShowFamily;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\Catalogue\WithCollectionSubNavigation;
use App\Actions\Catalogue\WithDepartmentSubNavigation;
use App\Actions\Catalogue\WithFamilySubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\UI\Catalogue\ShippingZoneSchemaTabsEnum;
use App\Http\Resources\Catalogue\ProductsResource;
use App\Http\Resources\Catalogue\ShippingZoneSchemasResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\ShippingZoneSchema;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexShippingZoneSchemas extends OrgAction
{
    use HasCatalogueAuthorisation;
    use WithDepartmentSubNavigation;
    use WithFamilySubNavigation;
    use WithCollectionSubNavigation;

    private Shop $parent;

    public function handle(Shop $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('products.name', $value)
                    ->orWhereStartWith('products.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(ShippingZoneSchema::class);

        if (class_basename($parent) == 'Shop') {
            $queryBuilder->where('shipping_zone_schemas.shop_id', $parent->id);
        } else {
            abort(419);
        }

        $queryBuilder
            ->defaultSort('shipping_zone_schemas.name')
            ->select([
                'shipping_zone_schemas.id',
                'shipping_zone_schemas.slug',
                'shipping_zone_schemas.name',
                'shipping_zone_schemas.type',
                'shipping_zone_schemas.created_at',
            ]);

        return $queryBuilder->allowedSorts(['name', 'status'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Shop $parent, ?array $modelOperations = null, $prefix = null, $canEdit = false): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Shop' => [
                            'title' => __("No schemas found"),
                        ],
                        default => null
                    }

                    /*
                    [
                        'title'       => __('no products'),
                        'description' => $canEdit ? __('Get started by creating a new product.') : null,
                        'count'       => $this->organisation->stats->number_products,
                        'action'      => $canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new product'),
                            'label'   => __('product'),
                            'route'   => [
                                'name'       => 'shops.products.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : null
                    ]*/
                );
            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $shippingZoneSchemas): AnonymousResourceCollection
    {
        return ProductsResource::collection($shippingZoneSchemas);
    }

    public function htmlResponse(LengthAwarePaginator $shippingZoneSchemas, ActionRequest $request): Response
    {
        $subNavigation = null;

        $title      = __('Shipping');
        $icon       = [
            'icon'  => ['fal', 'fa-cube'],
            'title' => __('shipping')
        ];
        $afterTitle = null;
        $iconRight  = null;
        $model      = null;

        if ($this->parent instanceof Shop) {
            $model = __('billables');
        }
        return Inertia::render(
            'Org/Catalogue/Shippings',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('shipping'),
                'pageHead'    => [
                    'title'         => $title,
                    'model'         => $model,
                    'icon'          => $icon,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'actions'       =>  [
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new shipping schema'),
                            'label'   => __('shipping schema'),
                            'route'   => [
                                'name'       => str_replace('index', 'create', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ]
                    ],
                    'subNavigation' => $subNavigation,
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => ShippingZoneSchemaTabsEnum::navigation(),
                ],
                'data'        => ShippingZoneSchemasResource::collection($shippingZoneSchemas),

                ShippingZoneSchemaTabsEnum::SCHEMAS->value => $this->tab == ShippingZoneSchemaTabsEnum::SCHEMAS->value ?
                    fn () => ShippingZoneSchemasResource::collection($shippingZoneSchemas)
                    : Inertia::lazy(fn () => ShippingZoneSchemasResource::collection($shippingZoneSchemas)),

            ]
        )->table($this->tableStructure(parent: $this->parent, prefix: ShippingZoneSchemaTabsEnum::SCHEMAS->value));
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $shop);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Shippings'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };


        return match ($routeName) {
            'grp.org.shops.show.assets.shipping.index' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            default => []
        };
    }
}
