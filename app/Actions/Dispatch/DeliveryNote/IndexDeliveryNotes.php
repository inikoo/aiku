<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\DeliveryNote;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Delivery\DeliveryNoteResource;
use App\Models\Central\Tenant;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Marketing\Shop;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexDeliveryNotes extends InertiaAction
{
    private Shop|Tenant $parent;
    public function handle($parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('delivery_notes.date', '~*', "\y$value\y")
                    ->orWhere('delivery_notes.number', '=', $value);
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::DELIVERY_NOTES->value);
        return QueryBuilder::for(DeliveryNote::class)
            ->defaultSort('delivery_notes.number')
            ->select(['delivery_notes.number', 'delivery_notes.date', 'delivery_notes.state', 'delivery_notes.created_at', 'delivery_notes.updated_at', 'delivery_notes.slug', 'shops.slug as shop_slug'])
            ->leftJoin('delivery_note_stats', 'delivery_notes.id', 'delivery_note_stats.delivery_note_id')
            ->leftJoin('shops', 'delivery_notes.shop_id', 'shops.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('delivery_notes.shop_id', $parent->id);
                }
            })
            ->allowedSorts(['number', 'date'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::DELIVERY_NOTES->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::DELIVERY_NOTES->value)
                ->pageName(TabsAbbreviationEnum::DELIVERY_NOTES->value.'Page');

            $table->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true);


            $table->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.products.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $delivery_notes): AnonymousResourceCollection
    {
        return DeliveryNoteResource::collection($delivery_notes);
    }


    public function htmlResponse(LengthAwarePaginator $delivery_notes, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());
        return Inertia::render(
            'Marketing/DeliveryNotes',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $parent
                ),
                'title'       => __('delivery notes'),
                'pageHead'    => [
                    'title' => __('delivery notes'),
                ],
                'delivery_notes' => DeliveryNoteResource::collection($delivery_notes),


            ]
        )->table($this->tableStructure($parent));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request);
        return $this->handle(app('currentTenant'));
    }

    public function InShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($shop);
    }

    public function getBreadcrumbs(string $routeName, Shop|Tenant $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('delivery-notes')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'delivery-notes.index'            => $headCrumb(),
            'shops.show.delivery-notes.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }
}
