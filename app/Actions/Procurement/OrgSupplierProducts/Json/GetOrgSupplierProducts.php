<?php
/*
 * author Arya Permana - Kirin
 * created on 11-11-2024-15h-50m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Procurement\OrgSupplierProducts\Json;

use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\UI\ShowOrgAgent;
use App\Actions\Procurement\UI\ShowProcurementDashboard;
use App\Http\Resources\Procurement\OrgSupplierProductsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class GetOrgSupplierProducts extends OrgAction
{
    private OrgSupplier|OrgAgent|Organisation $parent;

    public function handle(Organisation|OrgAgent|OrgSupplier $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('supplier_products.code', $value)
                    ->orWhereANyWordStartWith('supplier_products.name', $value);
            });
        });

        $queryBuilder = QueryBuilder::for(OrgSupplierProduct::class);
        $queryBuilder->leftJoin('supplier_products', 'supplier_products.id', 'org_supplier_products.supplier_product_id');


        if (class_basename($parent) == 'OrgAgent') {
            $queryBuilder->where('org_supplier_products.org_agent_id', $parent->id);
        } elseif (class_basename($parent) == 'OrgSupplier') {
            $queryBuilder->where('org_supplier_products.org_supplier_id', $parent->id);
        } else {
            $queryBuilder->where('org_supplier_products.organisation_id', $this->organisation->id);
        }


        return $queryBuilder
            ->defaultSort('supplier_products.code')
            ->select([
                'org_supplier_products.id',
                'supplier_products.code',
                'supplier_products.name'
            ])
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null)
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.view");
    }

    public function inOrgAgent(OrgAgent $orgAgent, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $orgAgent;
        $this->initialisation($orgAgent->organisation, $request);
        return $this->handle($orgAgent);
    }

    public function inOrgSupplier(OrgSupplier $orgSupplier, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $orgSupplier;
        $this->initialisation($orgSupplier->organisation, $request);
        return $this->handle($orgSupplier);
    }

    public function jsonResponse(LengthAwarePaginator $orgSupplierProducts): AnonymousResourceCollection
    {
        return OrgSupplierProductsResource::collection($orgSupplierProducts);
    }
}
