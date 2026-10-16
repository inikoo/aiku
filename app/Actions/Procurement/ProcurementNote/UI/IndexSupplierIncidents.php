<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\ProcurementNote\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\UI\ShowProcurementDashboard;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteCategoryEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteIncidentOutcomeEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteStateEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\ProcurementNote;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Incidents per supplier or agent for the organisation: how many, how many are still open, what was disputed and
 * what was recovered, in the group currency (INI-104). An incident on a supplier product or on a purchase order
 * counts for its supplier; one on an agent, or on an agent order with no supplier, for the agent.
 */
class IndexSupplierIncidents extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        abort_if($this->organisation->type === OrganisationTypeEnum::AGENT, 404);

        return $request->user()->authTo("procurement.{$this->organisation->id}.view");
    }

    public function handle(Organisation $organisation, ?string $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(fn ($query) => $query
                ->whereStartWith('procurement_notes.code', $value)
                ->orWhereAnyWordStartWith('procurement_notes.name', $value));
        });

        return QueryBuilder::for(ProcurementNote::query()->fromSub($this->rows($organisation), 'procurement_notes'))
            ->select('procurement_notes.*')
            ->defaultSort('-incidents')
            ->allowedSorts(['code', 'name', 'incidents', 'open', 'disputed', 'recovered', 'last_incident_at'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()?->getName())
            ->withQueryString();
    }

    /**
     * One row per supplier or agent with incidents, with the page of that supplier or agent in this organisation.
     */
    private function rows(Organisation $organisation): Builder
    {
        $incidents = DB::table('procurement_notes')
            ->leftJoin('supplier_products', function ($join) {
                $join->on('supplier_products.id', '=', 'procurement_notes.subject_id')->where('procurement_notes.subject_type', 'SupplierProduct');
            })
            ->leftJoin('purchase_orders', 'purchase_orders.id', '=', DB::raw("coalesce(procurement_notes.purchase_order_id, case when procurement_notes.subject_type = 'PurchaseOrder' then procurement_notes.subject_id end)"))
            ->where('procurement_notes.group_id', $organisation->group_id)
            ->where('procurement_notes.category', ProcurementNoteCategoryEnum::INCIDENT->value)
            ->where('procurement_notes.state', '!=', ProcurementNoteStateEnum::SUPERSEDED->value)
            ->whereRaw('coalesce(procurement_notes.organisation_id, purchase_orders.organisation_id, ?) = ?', [$organisation->id, $organisation->id])
            ->selectRaw("coalesce(case when procurement_notes.subject_type = 'Supplier' then procurement_notes.subject_id end, supplier_products.supplier_id, purchase_orders.supplier_id) as supplier_id")
            ->selectRaw("case when procurement_notes.subject_type = 'Agent' then procurement_notes.subject_id else purchase_orders.agent_id end as agent_id")
            ->addSelect(['procurement_notes.incident_outcome', 'procurement_notes.grp_incident_amount', 'procurement_notes.created_at']);

        $open     = ProcurementNoteIncidentOutcomeEnum::OPEN->value;
        $credited = ProcurementNoteIncidentOutcomeEnum::CREDITED->value;

        $parties = DB::query()->fromSub($incidents, 'incidents')
            ->selectRaw("case when incidents.supplier_id is not null then 'Supplier' else 'Agent' end as party_type")
            ->selectRaw('coalesce(incidents.supplier_id, incidents.agent_id) as party_id')
            ->selectRaw('count(*) as incidents')
            ->selectRaw('count(*) filter (where incidents.incident_outcome = ?) as open', [$open])
            ->selectRaw('coalesce(sum(incidents.grp_incident_amount), 0) as disputed')
            ->selectRaw('coalesce(sum(incidents.grp_incident_amount) filter (where incidents.incident_outcome = ?), 0) as recovered', [$credited])
            ->selectRaw('max(incidents.created_at) as last_incident_at')
            ->whereRaw('coalesce(incidents.supplier_id, incidents.agent_id) is not null')
            ->groupByRaw('1, 2');

        return DB::query()->fromSub($parties, 'parties')
            ->leftJoin('suppliers', function ($join) {
                $join->on('suppliers.id', '=', 'parties.party_id')->where('parties.party_type', 'Supplier');
            })
            ->leftJoin('agents', function ($join) {
                $join->on('agents.id', '=', 'parties.party_id')->where('parties.party_type', 'Agent');
            })
            ->leftJoin('org_suppliers', function ($join) use ($organisation) {
                $join->on('org_suppliers.supplier_id', '=', 'suppliers.id')->where('org_suppliers.organisation_id', $organisation->id);
            })
            ->leftJoin('org_agents', function ($join) use ($organisation) {
                $join->on('org_agents.agent_id', '=', 'agents.id')->where('org_agents.organisation_id', $organisation->id);
            })
            ->select('parties.*')
            ->selectRaw('coalesce(suppliers.code, agents.code) as code')
            ->selectRaw('coalesce(suppliers.name, agents.name) as name')
            ->selectRaw('org_suppliers.slug as org_supplier_slug, org_agents.slug as org_agent_slug');
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix.'Page');
            }

            $currency = $this->organisation->group->currency->code;

            $table
                ->withGlobalSearch()
                ->withLabelRecord([__('Supplier'), __('Suppliers')])
                ->withEmptyState(['title' => __('No incidents recorded')])
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'incidents', label: __('Incidents'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'open', label: __('Open'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'disputed', label: __('Disputed').' ('.$currency.')', canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'recovered', label: __('Recovered').' ('.$currency.')', canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'last_incident_at', label: __('Last incident'), canBeHidden: false, sortable: true, align: 'right')
                ->defaultSort('-incidents');
        };
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function htmlResponse(LengthAwarePaginator $incidents, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Procurement/SupplierIncidents',
            [
                'title'       => __('Supplier incidents'),
                'breadcrumbs' => array_merge(
                    ShowProcurementDashboard::make()->getBreadcrumbs($request->route()->originalParameters()),
                    [
                        [
                            'type'   => 'simple',
                            'simple' => [
                                'label' => __('Supplier incidents'),
                                'route' => [
                                    'name'       => 'grp.org.procurement.supplier_incidents.index',
                                    'parameters' => [$this->organisation->slug],
                                ],
                            ],
                        ],
                    ]
                ),
                'pageHead'    => [
                    'icon'  => ['title' => __('Supplier incidents'), 'icon' => 'fal fa-exclamation-triangle'],
                    'title' => __('Supplier incidents'),
                ],
                'data'        => $incidents->through(fn ($row) => [
                    'code'             => $row->code,
                    'name'             => $row->name,
                    'party_type'       => $row->party_type,
                    'incidents'        => (int) $row->incidents,
                    'open'             => (int) $row->open,
                    'disputed'         => (float) $row->disputed,
                    'recovered'        => (float) $row->recovered,
                    'last_incident_at' => $row->last_incident_at,
                    'route'            => match (true) {
                        (bool) $row->org_supplier_slug => ['name' => 'grp.org.procurement.org_suppliers.show', 'parameters' => [$this->organisation->slug, $row->org_supplier_slug]],
                        (bool) $row->org_agent_slug    => ['name' => 'grp.org.procurement.org_agents.show', 'parameters' => [$this->organisation->slug, $row->org_agent_slug]],
                        default                        => null,
                    },
                ]),
                'currency'    => $this->organisation->group->currency->code,
            ]
        )->table($this->tableStructure());
    }
}
