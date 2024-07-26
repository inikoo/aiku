<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\StoredItemAudit\StoredItemAuditStateEnum;
use App\Http\Resources\Fulfilment\StoredItemAuditsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexStoredItemAudits extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;
    use WithFulfilmentCustomerSubNavigation;

    private Fulfilment|Location $parent;

    private bool $selectStoredPallets = false;

    protected function getElementGroups(Fulfilment|FulfilmentCustomer|Warehouse $parent): array
    {
        return [
            'status' => [
                'label'    => __('Status'),
                'elements' => array_merge_recursive(
                    StoredItemAuditStateEnum::labels(),
                    StoredItemAuditStateEnum::count($parent)
                ),
                'engine' => function ($query, $elements) {
                    $query->whereIn('stored_item_audits.state', $elements);
                }
            ],
        ];
    }

    public function handle(Fulfilment|FulfilmentCustomer|Warehouse $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('stored_item_audits.slug', $value)
                    ->orWhereWith('stored_item_audits.reference', $value);
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(StoredItemAudit::class);


        switch (class_basename($parent)) {
            case "Fulfilment":
                $query->where('stored_item_audits.fulfilment_id', $parent->id);
                break;
            case "FulfilmentCustomer":
                $query->where('stored_item_audits.fulfilment_customer_id', $parent->id);
                break;
            case "Warehouse":
                $query->where('stored_item_audits.warehouse_id', $parent->id);
                break;
            default:
                abort(422);
        }

        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $query->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }


        $query->whereNotNull('stored_item_audits.slug');


        $query->defaultSort('stored_item_audits.id')
            ->select(
                'stored_item_audits.id',
                'stored_item_audits.slug',
                'stored_item_audits.reference',
                'stored_item_audits.public_notes',
                'stored_item_audits.internal_notes',
                'stored_item_audits.state',
                'stored_item_audits.completed_at',
                'stored_item_audits.in_process_at',
                'stored_item_audits.fulfilment_customer_id',
                'stored_item_audits.warehouse_id'
            );

        if ($parent instanceof Fulfilment) {
            $query->leftJoin('fulfilment_customers', 'fulfilment_customers.id', 'stored_item_audits.fulfilment_customer_id');
            $query->leftJoin('customers', 'customers.id', 'fulfilment_customers.customer_id');
            $query->addSelect('customers.name as fulfilment_customer_name', 'customers.slug as fulfilment_customer_slug');
        }

        return $query->allowedSorts(['stored_item_audits.id', 'stored_item_audits.reference', 'stored_item_audits.fulfilment_customer_name'])
            ->allowedFilters([$globalSearch, 'stored_item_audits.reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Fulfilment|FulfilmentCustomer|Warehouse $parent, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }

            $emptyStateData = [
                'icons' => ['fal fa-pallet'],
                'title' => __('No stored item audits found'),
                'count' => 0
            ];

            $emptyStateData['description'] = __("There is no stored item audits");

            $table->withGlobalSearch();

            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);

            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');

            if ($parent instanceof Organisation || $parent instanceof Fulfilment) {
                $table->column(key: 'fulfilment_customer_name', label: __('Customer'), canBeHidden: false, sortable: true, searchable: true);
            }

            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);

            $table->defaultSort('reference');
        };
    }

    public function jsonResponse(LengthAwarePaginator $storedItemAudits): AnonymousResourceCollection
    {
        return StoredItemAuditsResource::collection($storedItemAudits);
    }

    public function htmlResponse(LengthAwarePaginator $storedItemAudits, ActionRequest $request): Response
    {
        $subNavigation = [];

        $title      = __('Stored Item Audits');
        $icon       = ['fal', 'fa-pallet'];
        $afterTitle = null;
        $iconRight  = null;

        return Inertia::render(
            'Org/Fulfilment/StoredItemAudits',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Stored Item Audits'),
                'pageHead'    => [
                    'title'      => $title,
                    'afterTitle' => $afterTitle,
                    'iconRight'  => $iconRight,
                    'icon'       => $icon,

                    'subNavigation' => $subNavigation,
                    'actions'       => [
                        [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('New Audit'),
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-item-audits.create',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ],
                    ],
                ],
                'data'        => StoredItemAuditsResource::collection($storedItemAudits),
            ]
        )->table($this->tableStructure($this->parent, 'stored_item_audits'));
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment, 'stored_item_audits');
    }

    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment, 'stored_item_audits');
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.fulfilments.show.operations.stored-item-audits.index',
                            'parameters' => [
                                'organisation' => $routeParameters['organisation'],
                                'fulfilment'   => $routeParameters['fulfilment'],
                            ]
                        ],
                        'label' => __('Stored Item Audits'),
                        'icon'  => 'fal fa-bars',
                    ],
                ]
            ]
        );
    }
}
