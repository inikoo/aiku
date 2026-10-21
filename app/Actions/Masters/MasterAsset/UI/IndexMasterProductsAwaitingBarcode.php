<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Masters\MasterAsset\UI;

use App\Actions\Goods\UI\WithMasterCatalogueSubNavigation;
use App\Actions\Masters\MasterShop\UI\ShowMasterShop;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithMastersAuthorisation;
use App\Enums\Masters\MasterAsset\MasterAssetTypeEnum;
use App\Http\Resources\Masters\MasterProductsAwaitingBarcodeResource;
use App\InertiaTable\InertiaTable;
use App\Models\Masters\MasterAsset;
use App\Models\Masters\MasterShop;
use App\Services\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Masters the composition page asks a person to give a GTIN (several trade units, or one at a
 * quantity other than 1) that nobody has answered yet. A kit's shop products publish no barcode
 * until then, because the hydrator never guesses one for them.
 */
class IndexMasterProductsAwaitingBarcode extends OrgAction
{
    use WithMasterCatalogueSubNavigation;
    use WithMastersAuthorisation;

    private MasterShop $parent;

    private ?array $elementGroups = null;

    public const array KINDS = [
        'kit'  => 'count(*) > 1',
        'pack' => 'count(*) = 1 and max(model_has_trade_units.quantity) <> 1',
    ];

    public static function whereAwaitingBarcode(Builder|QueryBuilder $query): void
    {
        $query
            ->where('master_assets.is_main', true)
            ->where('master_assets.status', true)
            ->where('master_assets.type', MasterAssetTypeEnum::PRODUCT)
            ->where('master_assets.independent_barcode', false)
            ->where(fn ($query) => $query->whereNull('master_assets.barcode')->orWhere('master_assets.barcode', ''));

        self::whereComposition($query, array_keys(self::KINDS));
    }

    /**
     * @param  list<string>  $kinds  kit: several trade units, its products publish no barcode; pack: one trade unit at a quantity other than 1
     */
    public static function whereComposition(Builder|QueryBuilder $query, array $kinds): void
    {
        $having = collect(self::KINDS)->only($kinds)->map(fn (string $condition) => "($condition)")->implode(' or ') ?: 'false';

        $query->whereExists(
            fn ($query) => $query->select(DB::raw(1))
                ->from('model_has_trade_units')
                ->where('model_has_trade_units.model_type', 'MasterAsset')
                ->whereColumn('model_has_trade_units.model_id', 'master_assets.id')
                ->groupBy('model_has_trade_units.model_id')
                ->havingRaw($having)
        );
    }

    public function getElementGroups(MasterShop $parent): array
    {
        return $this->elementGroups ??= $this->buildElementGroups($parent);
    }

    private function buildElementGroups(MasterShop $parent): array
    {
        $query = MasterAsset::query()
            ->where('master_assets.master_shop_id', $parent->id)
            ->joinLateral(
                DB::table('model_has_trade_units')
                    ->selectRaw('count(*) as number_trade_units')
                    ->where('model_has_trade_units.model_type', 'MasterAsset')
                    ->whereColumn('model_has_trade_units.model_id', 'master_assets.id'),
                'composition'
            )
            ->selectRaw('count(*) filter (where composition.number_trade_units > 1) as kits, count(*) filter (where composition.number_trade_units = 1) as packs');

        self::whereAwaitingBarcode($query);

        $counts = $query->toBase()->first();

        return [
            'kind' => [
                'label'    => __('Composition'),
                'elements' => [
                    'kit'  => [__('Several trade units'), (int)$counts->kits],
                    'pack' => [__('One trade unit, other quantity'), (int)$counts->packs],
                ],
                'default'  => 'kit',
                'engine'   => fn ($query, array $elements) => self::whereComposition($query, $elements),
            ],
        ];
    }

    public function handle(MasterShop $parent, ?string $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('master_assets.code', $value)
                    ->orWhereStartWith('master_assets.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(MasterAsset::class)
            ->leftJoin('master_asset_stats', 'master_assets.id', '=', 'master_asset_stats.master_asset_id')
            ->leftJoin('master_product_categories as families', 'families.id', '=', 'master_assets.master_family_id')
            ->where('master_assets.master_shop_id', $parent->id);

        self::whereAwaitingBarcode($queryBuilder);

        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix,
                default: $elementGroup['default'],
            );
        }

        $queryBuilder->select([
            'master_assets.id',
            'master_assets.code',
            'master_assets.name',
            'master_assets.slug',
            'master_asset_stats.number_current_assets as used_in',
            'families.code as master_family_code',
            DB::raw("(select string_agg(case when model_has_trade_units.quantity = 1 then trade_units.code else trim_scale(model_has_trade_units.quantity)::text || ' × ' || trade_units.code end, ', ' order by trade_units.code)
                from model_has_trade_units join trade_units on trade_units.id = model_has_trade_units.trade_unit_id
                where model_has_trade_units.model_type = 'MasterAsset' and model_has_trade_units.model_id = master_assets.id) as trade_units"),
        ]);

        return $queryBuilder
            ->defaultSort('-used_in')
            ->allowedSorts(['code', 'name', 'used_in', 'master_family_code'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(MasterShop $parent, ?string $prefix = null): \Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements'],
                    default: $elementGroup['default'],
                );
            }

            $table
                ->withGlobalSearch()
                ->withEmptyState(['title' => __('Every master product has its GTIN')]);

            $table
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'trade_units', label: __('Trade units'), canBeHidden: false)
                ->column(key: 'master_family_code', label: __('Family'), sortable: true)
                ->column(key: 'used_in', label: __('Used in'), tooltip: __('Current products with this master'), canBeHidden: false, sortable: true, align: 'right')
                ->defaultSort('-used_in');
        };
    }

    public function jsonResponse(LengthAwarePaginator $masterAssets): AnonymousResourceCollection
    {
        return MasterProductsAwaitingBarcodeResource::collection($masterAssets);
    }

    public function htmlResponse(LengthAwarePaginator $masterAssets, ActionRequest $request): Response
    {
        $label = __('Master Products').' ('.__('Awaiting barcode').')';

        return Inertia::render(
            'Masters/MasterProductsAwaitingBarcode',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => $label,
                'pageHead'    => [
                    'title'         => $this->parent->name,
                    'is_negative'   => true,
                    'icon'          => [
                        'icon'  => ['fal', 'fa-store-alt'],
                        'title' => __('Master shop'),
                    ],
                    'model'         => '',
                    'afterTitle'    => ['label' => $label],
                    'iconRight'     => ['icon' => 'fal fa-barcode'],
                    'subNavigation' => $this->getMasterShopNavigation($this->parent),
                ],
                'data'        => MasterProductsAwaitingBarcodeResource::collection($masterAssets),
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, ?string $suffix = null): array
    {
        return match ($routeName) {
            'grp.masters.master_shops.show.master_products_awaiting_barcode' =>
            array_merge(
                ShowMasterShop::make()->getBreadcrumbs($this->parent),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => $routeName,
                                'parameters' => Arr::only($routeParameters, ['masterShop']),
                            ],
                            'label' => __('Master Products'),
                            'icon'  => 'fal fa-bars',
                        ],
                        'suffix' => trim('('.__('Awaiting barcode').') '.$suffix),
                    ],
                ]
            ),
            default => []
        };
    }

    public function inMasterShop(MasterShop $masterShop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $masterShop;
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($masterShop);
    }
}
