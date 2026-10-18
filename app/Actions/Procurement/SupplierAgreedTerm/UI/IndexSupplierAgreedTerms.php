<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 03:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierAgreedTerm\UI;

use App\Actions\Procurement\SupplierAgreedTerm\GetEffectiveSupplierAgreedTerms;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteVisibilityEnum;
use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermStateEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\SupplierAgreedTerm;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsObject;
use Spatie\QueryBuilder\AllowedFilter;

class IndexSupplierAgreedTerms
{
    use AsObject;

    /**
     * Every term ever agreed that bears on the subject, the ones in force first: a supplier lists
     * its own, its products' and its agent's; a supplier product its own, its supplier's and the
     * agent's. `in_force` tells the term that applies from one overridden by a closer one.
     */
    public function handle(Supplier|SupplierProduct|Agent $subject, ?string $prefix = null, ?Organisation $organisation = null, bool $forAgent = false): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $supplier = $subject instanceof SupplierProduct ? $subject->supplier : ($subject instanceof Supplier ? $subject : null);
        $agent    = $subject instanceof Agent ? $subject : $supplier?->agent;

        $inForce = GetEffectiveSupplierAgreedTerms::run($subject, $organisation)->pluck('id')->all();

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(fn ($query) => $query
                ->where('supplier_agreed_terms.value_text', 'ILIKE', "%$value%")
                ->orWhere('supplier_agreed_terms.term_type', 'ILIKE', '%'.str_replace(' ', '_', $value).'%')
                ->orWhere('supplier_agreed_terms.agreed_with', 'ILIKE', "%$value%"));
        });

        return QueryBuilder::for(SupplierAgreedTerm::class)
            ->leftJoin('supplier_products', function ($join) {
                $join->on('supplier_products.id', 'supplier_agreed_terms.subject_id')->where('supplier_agreed_terms.subject_type', 'SupplierProduct');
            })
            ->where(function ($query) use ($subject, $supplier, $agent) {
                foreach (array_filter([$subject, $supplier, $agent]) as $model) {
                    $query->orWhere(fn ($query) => $query->where('supplier_agreed_terms.subject_type', $model->getMorphClass())->where('supplier_agreed_terms.subject_id', $model->id));
                }
                if ($subject instanceof Supplier) {
                    $query->orWhere(fn ($query) => $query->where('supplier_agreed_terms.subject_type', 'SupplierProduct')->where('supplier_products.supplier_id', $subject->id));
                }
            })
            ->forOrganisation($organisation)
            ->when($forAgent, fn ($query) => $query->where('supplier_agreed_terms.visibility', ProcurementNoteVisibilityEnum::AGENT->value))
            ->select(['supplier_agreed_terms.*', 'supplier_products.code as product_code'])
            ->selectRaw(
                "supplier_agreed_terms.state = ? and (supplier_agreed_terms.subject_type = 'SupplierProduct' and supplier_agreed_terms.subject_id != ? or supplier_agreed_terms.id = any(?::int[])) as in_force",
                [SupplierAgreedTermStateEnum::ACTIVE->value, $subject instanceof SupplierProduct ? $subject->id : 0, '{'.implode(',', $inForce).'}']
            )
            ->with(['currency', 'agreedBy', 'organisation', 'attachments'])
            ->orderByRaw('supplier_agreed_terms.state = ? desc', [SupplierAgreedTermStateEnum::ACTIVE->value])
            ->orderBy('supplier_products.code')
            ->orderBy('supplier_agreed_terms.term_type')
            ->orderByDesc('supplier_agreed_terms.id')
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()?->getName())
            ->withQueryString();
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withEmptyState(['title' => __('No terms agreed yet')])
                ->column(key: 'term', label: __('Term'), canBeHidden: false)
                ->column(key: 'value', label: __('Agreed'), canBeHidden: false)
                ->column(key: 'applies_to', label: __('Applies to'), canBeHidden: false)
                ->column(key: 'agreed_on', label: __('Agreed on'), canBeHidden: false)
                ->column(key: 'evidence', label: __('Evidence'), canBeHidden: false)
                ->column(key: 'shown', label: __('Shown to'), canBeHidden: false)
                ->column(key: 'state', label: __('State'), canBeHidden: false)
                ->column(key: 'actions', label: '', canBeHidden: false);
        };
    }
}
