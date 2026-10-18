<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Http\Resources\Procurement\AgentSalesProductsResource;
use App\InertiaTable\InertiaTable;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Every supplier product of the agent, added up over the organisations that buy it: what it sold at cost, the worth of
 * its stock and, per organisation, how it stands (out of stock, about to run out, not selling). Narrowed to one
 * supplier with ?supplier=<id> and to one problem with the problem element filter.
 */
class IndexAgentSalesProducts extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementAuthorisation;

    private ?int $supplierId = null;

    private GetAgentSalesProducts $valuation;

    /**
     * @return array<string, array{label: string, elements: array<string, array{0: string, 1: null}>, engine: Closure}>
     */
    private function getElementGroups(): array
    {
        return [
            'problem' => [
                'label'    => __('Stock'),
                'elements' => [
                    'out'  => [__('Out of stock'), null],
                    'low'  => [__('About to run out'), null],
                    'dead' => [__('Not selling'), null],
                    'none' => [__('No problem'), null],
                ],
                'engine'   => function ($query, array $elements) {
                    $query->where(function ($query) use ($elements) {
                        foreach ($elements as $element) {
                            match ($element) {
                                'out'   => $query->orWhere('has_out', true),
                                'low'   => $query->orWhere('has_low', true),
                                'dead'  => $query->orWhere('has_dead', true),
                                default => $query->orWhereRaw('not (has_out or has_low or has_dead)'),
                            };
                        }
                    });
                },
            ],
        ];
    }

    public function handle(Agent $agent, ?int $supplierId): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $like = '%'.addcslashes((string) $value, '%_\\').'%';
            $query->whereRaw('(code collate "C" ilike ? or name ilike ?)', [$like, $like]);
        });
        $supplierFilter = AllowedFilter::callback('supplier', fn ($query, $value) => $query->where('supplier_id', (int) $value));

        $query = QueryBuilder::for(SupplierProduct::withoutGlobalScopes()->fromSub($this->valuation->products($agent, $supplierId, interval: $this->dateInterval), 'products')->selectRaw('products.*, '.GetAgentSalesProducts::DERIVED));

        foreach ($this->getElementGroups() as $key => $elementGroup) {
            $query->whereElementGroup(key: $key, allowedElements: array_keys($elementGroup['elements']), engine: $elementGroup['engine']);
        }

        return $query
            ->defaultSort('-sold')
            ->allowedSorts(['code', 'name', 'supplier_code', 'sold', 'stock_value', ...GetAgentSalesProducts::make()->derivedSorts()])
            ->allowedFilters([$globalSearch, $supplierFilter])
            ->orderBy('id')
            ->withPaginator(null, tableName: request()->route()?->getName())
            ->withQueryString();
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->dateInterval($this->dateInterval)
                ->withGlobalSearch()
                ->withLabelRecord([__('Product'), __('Products')])
                ->withEmptyState(['title' => __('No products found')]);

            foreach ($this->getElementGroups() as $key => $elementGroup) {
                $table->elementGroup(key: $key, label: $elementGroup['label'], elements: $elementGroup['elements']);
            }

            $table
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true)
                ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true)
                ->column(key: 'supplier_code', label: __('Supplier'), canBeHidden: false, sortable: true)
                ->column(key: 'sold', label: $this->valuation->soldLabel(), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'last_year', label: $this->valuation->lastYearLabel(), canBeHidden: false, align: 'right')
                ->column(key: 'change_percent', label: __('Change'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'rating', label: __('Rating'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'refund_percent', label: __('Refunds %'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'short_percent', label: __('Short deliveries %'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'stock_value', label: __('Stock, at cost'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'organisations', label: __('Organisations'), canBeHidden: false);
        };
    }

    public function asController(Organisation $organisation, ActionRequest $request): Agent
    {
        $this->initialisation($organisation, $request);
        $this->dateInterval = GetAgentSalesProducts::make()->interval($request->input('dateInterval'));
        $this->valuation    = GetAgentSalesProducts::make()->valuedBy($request->input('currency'), $request->input('measure'));
        $agent = $this->getOrganisationAgent($organisation);
        abort_unless($agent, 404);

        $supplierId = ((int) ($request->input('filter.supplier') ?: $request->input('supplier'))) ?: null;
        abort_if($supplierId && !Supplier::where('id', $supplierId)->where('agent_id', $agent->id)->exists(), 404);
        $this->supplierId = $supplierId;

        return $agent;
    }

    public function htmlResponse(Agent $agent, ActionRequest $request): Response
    {
        $parameters = $request->route()->originalParameters();
        $supplier   = $this->supplierId ? Supplier::find($this->supplierId) : null;
        $trail      = $supplier ? [
            [
                'type'   => 'simple',
                'simple' => [
                    'label' => __('Suppliers'),
                    'route' => ['name' => 'grp.org.agent.sales.suppliers.index', 'parameters' => $parameters],
                ],
            ],
            [
                'type'   => 'simple',
                'simple' => [
                    'label' => $supplier->code,
                    'route' => ['name' => 'grp.org.agent.sales.suppliers.show', 'parameters' => [...$parameters, 'supplier' => $supplier->slug]],
                ],
            ],
        ] : [];

        return Inertia::render(
            'Org/Procurement/AgentSalesProducts',
            [
                'title'       => __('Products'),
                'breadcrumbs' => ShowAgentSales::make()->getBreadcrumbs($parameters, [
                    ...$trail,
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'label' => __('Products'),
                            'route' => ['name' => 'grp.org.agent.sales.products.index', 'parameters' => $parameters],
                        ],
                    ],
                ]),
                'pageHead'    => [
                    'icon'  => ['title' => __('Products'), 'icon' => 'fal fa-box-usd'],
                    'title' => __('Products'),
                    'model' => $supplier?->name ?? __('Sales'),
                ],
                'currency'    => $this->valuation->agentCurrency($agent)[0]->code,
                'valuation'   => $this->valuation->valuation($agent),
                'data'        => AgentSalesProductsResource::collection($this->handle($agent, $this->supplierId)),
            ]
        )->table($this->tableStructure());
    }
}
