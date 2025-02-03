<?php

/*
 * author Arya Permana - Kirin
 * created on 04-11-2024-13h-41m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Retina\Spaces\UI;

use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\RetinaAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Http\Resources\Fulfilment\SpacesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Space;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexRetinaSpaces extends RetinaAction
{
    use WithFulfilmentCustomerSubNavigation;
    use HasFulfilmentAssetsAuthorisation;

    public function handle(Fulfilment|Organisation|Group|FulfilmentCustomer $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartsWith('spaces.reference', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(Space::class);


        if ($parent instanceof Fulfilment) {
            $query->where('spaces.fulfilment_id', $parent->id);
        } elseif ($parent instanceof Group) {
            $query->where('spaces.group_id', $parent->id);
        } elseif ($parent instanceof Organisation) {
            $query->where('spaces.organisation_id', $parent->id);
        } elseif ($parent instanceof FulfilmentCustomer) {
            $query->where('spaces.fulfilment_customer_id', $parent->id);
        } else {
            abort(419);
        }
        $query->leftjoin('rentals', 'spaces.rental_id', '=', 'rentals.id');
        $query->leftjoin('currencies', 'rentals.currency_id', '=', 'currencies.id');
        $query->leftjoin('recurring_bills', 'spaces.current_recurring_bill_id', '=', 'recurring_bills.id');

        return $query->defaultSort('spaces.reference')
            ->select([
                'spaces.id',
                'spaces.reference',
                'spaces.slug',
                'spaces.state',
                'spaces.start_at',
                'spaces.end_at',
                'rentals.slug as rental_slug',
                'rentals.name as rental_name',
                'rentals.code as rental_code',
                'rentals.price as rental_price',
                'rentals.unit as rental_unit',
                'currencies.symbol as currency_symbol',
            ])
            ->allowedSorts(['id', 'reference', 'state', 'start_at', 'end_at'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }


    public function tableStructure($parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix . 'Page');
            }


            $noResults = __("No purges found");

            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => $noResults,
                    ]
                );


            $table->column(key: 'state', label: __('state'), sortable: true, canBeHidden: false, searchable: true);
            $table->column(key: 'reference', label: __('reference'), sortable: false, canBeHidden: false, searchable: false);
            $table->column(key: 'rental', label: __('rental'), sortable: true, canBeHidden: false, searchable: true);
            $table->column(key: 'start_at', label: __('start'), sortable: true, canBeHidden: false, searchable: true);
            $table->column(key: 'end_at', label: __('end'), sortable: true, canBeHidden: false, searchable: true);

        };
    }

    public function jsonResponse(LengthAwarePaginator $spaces): AnonymousResourceCollection
    {
        return SpacesResource::collection($spaces);
    }

    public function htmlResponse(LengthAwarePaginator $spaces, ActionRequest $request): Response
    {
        $subNavigation = $this->getFulfilmentCustomerSubNavigation($this->fulfilmentCustomer, $request);
        $icon = ['fal', 'fa-user'];
        $title = $this->fulfilmentCustomer->customer->name;
        $iconRight = [
            'icon' => 'fal fa-parking',
        ];
        $afterTitle = [

            'label' => __('Spaces')
        ];


        return Inertia::render(
            'Space/RetinaSpaces',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title' => $title,
                'pageHead' => [
                    'title' => $title,
                    'afterTitle' => $afterTitle,
                    'iconRight' => $iconRight,
                    'icon' => $icon,
                   /*  'subNavigation' => $subNavigation,
                    'actions' => [
                        [
                            'type' => 'button',
                            'style' => 'create',
                            'label' => __('space'),
                            'route' => [
                                'name' => 'grp.org.fulfilments.show.crm.customers.show.spaces.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ], */
                ],
                'data' => SpacesResource::collection($spaces)
            ]
        )->table(
            $this->tableStructure(
                parent: $this->fulfilmentCustomer,
            )
        );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(parent: $this->fulfilmentCustomer);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return [
            [
                'type' => 'simple',
                'simple' => [
                    'route' => $routeParameters,
                    'label' => __('Spaces'),
                    'icon' => 'fal fa-bars'
                ],
            ],
        ];
    }
}
