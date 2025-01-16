<?php

/*
 * author Arya Permana - Kirin
 * created on 16-01-2025-14h-01m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\StoredItemAuditDelta\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Http\Resources\Fulfilment\StoredItemAuditDeltasResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\Fulfilment\StoredItemAuditDelta;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexStoredItemAuditDeltas extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;

    private StoredItemAudit $storedItemAudit;


    public function handle(StoredItemAudit $storedItemAudit, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('stored_item_audit_deltas.state', $value)
                    ->orWhereWith('stored_item_audit_deltas.audit_type', $value);
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(StoredItemAuditDelta::class);

        $query->where('stored_item_audit_deltas.stored_item_audit_id', $storedItemAudit->id);

        $query->leftjoin('pallets', 'stored_item_audit_deltas.pallet_id', '=', 'pallets.id');
        $query->leftjoin('stored_items', 'stored_item_audit_deltas.stored_item_id', '=', 'stored_items.id');

        $query->defaultSort('stored_item_audit_deltas.id')
            ->select(
                'stored_item_audit_deltas.id',
                'stored_item_audit_deltas.pallet_id as pallet_id',
                'pallets.customer_reference as pallet_customer_reference',
                'stored_item_audit_deltas.stored_item_id as stored_item_id',
                'stored_items.reference as stored_item_reference',
                'stored_item_audit_deltas.audited_at',
                'stored_item_audit_deltas.original_quantity',
                'stored_item_audit_deltas.audited_quantity',
                'stored_item_audit_deltas.state',
                'stored_item_audit_deltas.audit_type',
            );


        return $query->allowedSorts(['id', 'audited_at', 'original_quantity','audited_quantity', 'state', 'audit_type'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function jsonResponse(LengthAwarePaginator $storedItemAuditDeltas): AnonymousResourceCollection
    {
        return StoredItemAuditDeltasResource::collection($storedItemAuditDeltas);
    }

    public function tableStructure($prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }


            $emptyStateData = [
                'icons' => ['fal fa-pallet'],
                'title' => __('No audits found'),
            ];


            $table->withGlobalSearch();

            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);

            $table->column(key: 'state', label: __('state'), canBeHidden: false, searchable: true)
                ->column(key: 'original_quantity', label: __('original quantity'), canBeHidden: false, searchable: true)
                ->column(key: 'audited_quantity', label: __('audited quantity'), canBeHidden: false, searchable: true)
                ->column(key: 'audit_type', label: __('audit type'), canBeHidden: false, searchable: true)
                ->column(key: 'audited_at', label: __('audited at'), canBeHidden: false, searchable: true);
        };
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, StoredItemAudit $storedItemAudit, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($storedItemAudit);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, StoredItemAudit $storedItemAudit, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($storedItemAudit);
    }
}
