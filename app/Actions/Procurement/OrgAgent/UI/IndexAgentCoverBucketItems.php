<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 31 Aug 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\GetAgentOrderCapacity;
use App\Actions\Procurement\OrgAgent\GetAgentStockCoverBuckets;
use App\Actions\Procurement\OrgAgent\WithAgentShoppingSubNavigation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\Procurement\ShoppingListItem\ShoppingListItemStateEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SysAdmin\Organisation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexAgentCoverBucketItems extends OrgAction
{
    use WithProcurementAuthorisation;
    use WithAgentShoppingSubNavigation;

    private OrgAgent $orgAgent;
    private string $bucket;
    private ?string $rank;
    private ?int $supplierId;

    public function handle(OrgAgent $orgAgent, string $bucket, ?string $rank, ?int $supplierId): LengthAwarePaginator
    {
        $ids = GetAgentStockCoverBuckets::make()->orgSupplierProductIdsInBucket($orgAgent, $bucket, $rank, $supplierId);

        $paginator = OrgSupplierProduct::query()
            ->whereIn('id', $ids)
            ->select(['id', 'slug', 'supplier_product_id'])
            ->when(
                $ids,
                fn ($query) => $query->orderByRaw(
                    'array_position(array['.implode(',', array_map(intval(...), $ids)).']::bigint[], id)'
                )
            )
            ->paginate(25)
            ->withQueryString();

        return $this->hydrate($paginator);
    }

    private function hydrate(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $orgSupplierProductIds = collect($paginator->items())->pluck('id');

        $details = DB::table('org_supplier_products as osp')
            ->join('supplier_products as sp', 'sp.id', 'osp.supplier_product_id')
            ->join('suppliers as sup', 'sup.id', 'sp.supplier_id')
            ->leftJoin('currencies as cur', 'cur.id', 'sp.currency_id')
            ->leftJoinLateral(GetAgentStockCoverBuckets::bestOrgStock(), 'os')
            ->leftJoin('org_stock_stats as s', 's.org_stock_id', 'os.id')
            ->whereIn('osp.id', $orgSupplierProductIds)
            ->select([
                'osp.id',
                'sp.code',
                'sp.name',
                'sp.cost',
                'sp.units_per_carton',
                'sp.minimum_carton_order',
                'sp.measured_lead_time_days',
                'sp.estimated_lead_time_days',
                'sup.code as supplier_code',
                'sup.slug as supplier_slug',
                'cur.code as currency',
                'os.id as org_stock_id',
                'os.quantity_available',
                's.days_of_cover',
                's.recommended_order_quantity',
            ])
            ->get()
            ->keyBy('id');

        $webImages = DB::table('product_has_org_stocks')
            ->join('products', 'products.id', 'product_has_org_stocks.product_id')
            ->whereIn('product_has_org_stocks.org_stock_id', $details->pluck('org_stock_id')->filter())
            ->whereNull('products.deleted_at')
            ->whereNotNull('products.web_images->main')
            ->orderBy('products.id')
            ->pluck('products.web_images', 'product_has_org_stocks.org_stock_id');

        $openItems = DB::table('shopping_list_items')
            ->whereIn('org_supplier_product_id', $orgSupplierProductIds)
            ->where('state', ShoppingListItemStateEnum::OPEN->value)
            ->whereNull('deleted_at')
            ->select(['id', 'org_supplier_product_id', 'quantity_units'])
            ->get()
            ->keyBy('org_supplier_product_id');

        $paginator->getCollection()->transform(function (OrgSupplierProduct $orgSupplierProduct) use ($details, $openItems, $webImages) {
            $detail   = $details->get($orgSupplierProduct->id);
            $openItem = $openItems->get($orgSupplierProduct->id);
            $images   = json_decode($webImages->get($detail?->org_stock_id) ?? 'null', true);

            return [
                'id'                     => $orgSupplierProduct->id,
                'slug'                   => $orgSupplierProduct->slug,
                'code'                   => $detail?->code,
                'name'                   => $detail?->name,
                'image'                  => Arr::get($images, 'main.gallery') ?? Arr::get($images, 'main.thumbnail'),
                'supplier_code'          => $detail?->supplier_code,
                'supplier_slug'          => $detail?->supplier_slug,
                'lead_time_days'         => $detail?->measured_lead_time_days ?? $detail?->estimated_lead_time_days,
                'lead_time_measured'     => $detail?->measured_lead_time_days !== null,
                'our_stock'              => $detail?->quantity_available !== null ? (float) $detail->quantity_available : null,
                'our_days_of_cover'      => $detail?->days_of_cover !== null ? (int) $detail->days_of_cover : null,
                'recommended_quantity'   => $detail?->recommended_order_quantity !== null
                    ? (int) ceil((float) $detail->recommended_order_quantity)
                    : null,
                'units_per_carton'       => $detail?->units_per_carton ? (int) $detail->units_per_carton : null,
                'minimum_carton_order'   => $detail?->minimum_carton_order ? (int) $detail->minimum_carton_order : null,
                'cost'                   => $detail?->cost !== null ? (float) $detail->cost : null,
                'currency'               => $detail?->currency,
                'shopping_list_item_id'  => $openItem?->id,
                'ordered_quantity'       => $openItem ? (float) $openItem->quantity_units : 0,
            ];
        });

        return $paginator;
    }

    /**
     * @param  array{list: array{value: float, lines: int, units: float}, currency: string}  $orderCapacity
     * @return array{partner_name: string, count: int, total: float, currency: string, items: array<int, object>, listRoute: array{name: string, parameters: array<int, string>}}
     */
    private function miniCart(array $orderCapacity): array
    {
        return [
            'partner_name' => $this->orgAgent->agent->name,
            'count'        => $orderCapacity['list']['lines'],
            'total'        => $orderCapacity['list']['value'],
            'currency'     => $orderCapacity['currency'],
            'items'        => DB::table('shopping_list_items as sli')
                ->join('org_supplier_products as osp', 'osp.id', 'sli.org_supplier_product_id')
                ->join('supplier_products as sp', 'sp.id', 'osp.supplier_product_id')
                ->join('suppliers as sup', 'sup.id', 'sp.supplier_id')
                ->where('sli.organisation_id', $this->orgAgent->organisation_id)
                ->where('sli.agent_id', $this->orgAgent->agent_id)
                ->where('sli.state', ShoppingListItemStateEnum::OPEN->value)
                ->whereNull('sli.deleted_at')
                ->orderByDesc('sli.id')
                ->limit(40)
                ->selectRaw('sli.id, sp.code as org_stock_code, sp.name as org_stock_name, sup.code as family_name, ceil(sli.quantity_units / greatest(coalesce(sp.units_per_carton, 1), 1))::int as quantity')
                ->get()
                ->all(),
            'listRoute'    => [
                'name'       => 'grp.org.procurement.shopping_list.index',
                'parameters' => [$this->orgAgent->organisation->slug],
            ],
        ];
    }

    private function bucketLabel(): string
    {
        return GetAgentStockCoverBuckets::make()->bucketLabel($this->bucket);
    }

    public function asController(Organisation $organisation, OrgAgent $orgAgent, ActionRequest $request): LengthAwarePaginator
    {
        $this->orgAgent   = $orgAgent;
        $cover            = $request->input('cover', 'out');
        $this->bucket     = array_key_exists($cover, GetAgentStockCoverBuckets::BUCKETS) ? $cover : 'out';
        $rank             = $request->input('rank');
        $this->rank       = in_array($rank, ['A', 'B', 'C', 'D', 'Z'], true) ? $rank : null;
        $this->supplierId = $request->input('supplier') ? (int) $request->input('supplier') : null;

        $this->initialisation($organisation, $request);

        return $this->handle($orgAgent, $this->bucket, $this->rank, $this->supplierId);
    }

    public function htmlResponse(LengthAwarePaginator $items, ActionRequest $request): Response
    {
        $orderCapacity = GetAgentOrderCapacity::run($this->orgAgent);

        return Inertia::render(
            'Procurement/AgentCoverBucketItems',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->orgAgent, $request->route()->originalParameters()),
                'title'       => $this->bucketLabel(),
                'pageHead'    => [
                    'icon'          => [
                        'icon'  => ['fal', 'fa-shopping-basket'],
                        'title' => $this->bucketLabel(),
                    ],
                    'model'         => $this->orgAgent->agent->name,
                    'title'         => $this->bucketLabel().($this->rank ? ' · '.$this->rank : ''),
                    'subNavigation' => $this->getAgentShoppingNavigation($this->orgAgent),
                ],
                'orgAgent'    => [
                    'id'       => $this->orgAgent->id,
                    'slug'     => $this->orgAgent->slug,
                    'currency' => $orderCapacity['currency'],
                ],
                'addRoute'    => [
                    'name'       => 'grp.org.procurement.shopping_list.store',
                    'parameters' => [$this->orgAgent->organisation->slug],
                ],
                'bucket'      => $this->bucket,
                'bucketLabel' => $this->bucketLabel(),
                'rank'        => $this->rank,
                'items'       => $items,
                'miniCart'    => $this->miniCart($orderCapacity),
            ]
        );
    }

    public function getBreadcrumbs(OrgAgent $orgAgent, array $routeParameters): array
    {
        return array_merge(
            ShowAgentShoppingDashboard::make()->getBreadcrumbs($orgAgent, $routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.procurement.org_agents.show.shopping.items.index',
                            'parameters' => $routeParameters,
                        ],
                        'label' => $this->bucketLabel(),
                        'icon'  => 'fal fa-shopping-basket',
                    ],
                ],
            ]
        );
    }
}
