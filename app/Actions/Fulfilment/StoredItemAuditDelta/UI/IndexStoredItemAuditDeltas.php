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
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaStateEnum;
use App\Http\Resources\Fulfilment\StoredItemAuditDeltasResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItem;
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


    public function handle(StoredItemAudit|StoredItem $parent, $prefix = null): LengthAwarePaginator
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


        $query->leftjoin('pallets', 'stored_item_audit_deltas.pallet_id', '=', 'pallets.id');
        $query->leftjoin('stored_items', 'stored_item_audit_deltas.stored_item_id', '=', 'stored_items.id');

        if ($parent instanceof StoredItem) {
            $query->where('stored_item_audit_deltas.stored_item_id', $parent->id)
            ->where('stored_item_audit_deltas.state', StoredItemAuditDeltaStateEnum::COMPLETED->value);
            $query->leftJoin('stored_item_audits', 'stored_item_audits.id', 'stored_item_audit_deltas.stored_item_audit_id');
            $query->leftJoin('stored_item_movements', 'stored_item_movements.stored_item_audit_delta_id', 'stored_item_audit_deltas.id');
            $query->leftJoin('pallet_deliveries', 'pallet_deliveries.id', 'stored_item_movements.pallet_delivery_id');
            $query->leftJoin('pallet_returns', 'pallet_returns.id', 'stored_item_movements.pallet_return_id');

        } else {
            $query->where('stored_item_audit_deltas.stored_item_audit_id', $parent->id);
        }


        $query->defaultSort('-stored_item_audit_deltas.audited_at')
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
                'stored_item_audits.reference as stored_item_audit_reference',
                'pallet_deliveries.reference as pallet_delivery_reference',
            );


        return $query->allowedSorts(['id', 'audited_at', 'original_quantity','audited_quantity', 'state', 'audit_type'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function jsonResponse(LengthAwarePaginator $storedItemAuditDeltas): AnonymousResourceCollection
    {
        return StoredItemAuditDeltasResource::collection($storedItemAuditDeltas);
    }

    public function tableStructure(StoredItemAudit|StoredItem $parent, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix, $modelOperations) {
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
            $table->defaultSort('-audited_at');

            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);

            $table->column(key: 'state', label: __('state'), canBeHidden: false, searchable: true, type: 'icon')
                ->column(key: 'pallet_customer_reference', label: __('pallet'), canBeHidden: false, searchable: true);
            if ($parent instanceof StoredItemAudit) {
                $table->column(key: 'stored_item_reference', label: __('stored item'), canBeHidden: false, searchable: true);
            } elseif ($parent instanceof StoredItem) {
                $table->column(key: 'description', label: __('parent'), canBeHidden: false, searchable: true);
            }

            $table->column(key: 'original_quantity', label: __('original quantity'), canBeHidden: false, searchable: true)
                ->column(key: 'audited_quantity', label: __('audited quantity'), canBeHidden: false, searchable: true)
                ->column(key: 'audit_type_label', label: __('audit type'), canBeHidden: false, searchable: true)
                ->column(key: 'audited_at', label: __('audited at'), canBeHidden: false, searchable: true, sortable: true);
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
