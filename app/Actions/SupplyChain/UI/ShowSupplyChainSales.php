<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\UI\GetAgentSalesFeedback;
use App\Actions\Procurement\OrgAgent\UI\GetAgentSalesProducts;
use App\Actions\Procurement\OrgAgent\UI\ShowAgentSales;
use App\Actions\SupplyChain\Agent\UI\ShowAgent;
use App\Actions\SupplyChain\Agent\WithAgentSubNavigation;
use App\Actions\SupplyChain\Supplier\UI\ShowSupplier;
use App\Actions\SupplyChain\Supplier\WithSupplierSubNavigation;
use App\Actions\SupplyChain\SupplierShareLink\StoreSupplierShareLink;
use App\Actions\Traits\Authorisations\WithSupplyChainAuthorisation;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierShareLink;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * How a supplier's or an agent's products sell in our organisations, for the buyers sitting with them: the agent
 * sales dashboard read from supply chain, in the seller's own currency or the group's.
 */
class ShowSupplyChainSales extends OrgAction
{
    use WithAgentSubNavigation;
    use WithSupplierSubNavigation;
    use WithSupplyChainAuthorisation;

    private GetAgentSalesProducts $valuation;

    public function inSupplier(Supplier $supplier, ActionRequest $request): Supplier
    {
        $this->initialise($request);

        return $supplier;
    }

    public function inAgent(Agent $agent, ActionRequest $request): Agent
    {
        $this->initialise($request);

        return $agent;
    }

    private function initialise(ActionRequest $request): void
    {
        $this->initialisationFromGroup(app('group'), $request);
        $this->valuation = GetAgentSalesProducts::make()->valuedBy($request->input('currency'), $request->input('measure'), [$this->group->currency->code]);
    }

    /**
     * The private links the supplier was given, newest first, with who opened them lately.
     *
     * @return array<string, mixed>
     */
    private function share(Supplier $supplier): array
    {
        return [
            'can_create'  => $this->canEdit,
            'email'       => $supplier->email,
            'months'      => StoreSupplierShareLink::MONTHS,
            'store_route' => ['name' => 'grp.models.supplier.share_links.store', 'parameters' => ['supplier' => $supplier->id]],
            'links'       => SupplierShareLink::where('supplier_id', $supplier->id)
                ->with(['createdBy:id,contact_name', 'views' => fn ($query) => $query->latest('created_at')->limit(5)])
                ->latest()
                ->get()
                ->map(fn (SupplierShareLink $link) => [
                    'id'             => $link->id,
                    'live'           => $link->isLive(),
                    'created_at'     => $link->created_at,
                    'created_by'     => $link->createdBy?->contact_name,
                    'expires_at'     => $link->expires_at,
                    'revoked_at'     => $link->revoked_at,
                    'email'          => $link->email,
                    'number_views'   => $link->number_views,
                    'last_viewed_at' => $link->last_viewed_at,
                    'views'          => $link->views->map(fn ($view) => ['ip' => $view->ip, 'at' => $view->created_at])->all(),
                    'revoke_route'   => ['name' => 'grp.models.supplier_share_link.revoke', 'parameters' => ['supplierShareLink' => $link->id]],
                ])
                ->all(),
        ];
    }

    public function htmlResponse(Agent|Supplier $seller, ActionRequest $request): Response
    {
        $isSupplier = $seller instanceof Supplier;
        $title      = $isSupplier ? $seller->name : $seller->organisation->name;

        return Inertia::render(
            'Org/Procurement/AgentSales',
            [
                'title'       => __('Sales').' '.$seller->code,
                'breadcrumbs' => $isSupplier
                    ? ShowSupplier::make()->getBreadcrumbs($seller, 'grp.supply-chain.suppliers.show', [], '('.__('Sales').')')
                    : ShowAgent::make()->getBreadcrumbs($seller, 'grp.supply-chain.agents.show', [], '('.__('Sales').')'),
                'pageHead'    => [
                    'title'         => $title,
                    'model'         => __('Sales'),
                    'icon'          => [
                        'title' => __('Sales'),
                        'icon'  => 'fal fa-chart-line',
                    ],
                    'subNavigation' => $isSupplier ? $this->getSupplierNavigation($seller) : $this->getAgentNavigation($seller),
                ],
                'seller'      => $isSupplier ? 'supplier' : 'agent',
                'staff'       => true,
                'share'       => $isSupplier && !$seller->agent_id ? $this->share($seller) : null,
                'supplier'    => null,
                'links'       => null,
                'valuation'   => $this->valuation->valuation($seller),
                'sales'       => Inertia::defer(fn () => ShowAgentSales::make()->handle($seller, null, $this->valuation)),
                'feedback'    => Inertia::defer(fn () => GetAgentSalesFeedback::run($seller), 'feedback'),
            ]
        );
    }
}
