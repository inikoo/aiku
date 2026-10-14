<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 02:10:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierAgreedTerm;

use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermTypeEnum;
use App\Models\Procurement\SupplierAgreedTerm;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

class GetEffectiveSupplierAgreedTerms
{
    use AsObject;

    /**
     * The terms in force, one per term type: the supplier product's own term beats the supplier's,
     * which beats the agent's, and within each a term for this organisation beats the one for every
     * organisation. Other charges are all kept, since each is a different charge. Every term comes
     * back with a `level` attribute naming where it was agreed.
     *
     * @return Collection<int, SupplierAgreedTerm>
     */
    public function handle(Supplier|SupplierProduct|Agent $subject, ?Organisation $organisation = null): Collection
    {
        $supplier = $subject instanceof SupplierProduct ? $subject->supplier : ($subject instanceof Supplier ? $subject : null);
        $agent    = $subject instanceof Agent ? $subject : $supplier?->agent;

        $levels = collect([
            'supplier_product' => $subject instanceof SupplierProduct ? $subject : null,
            'supplier'         => $supplier,
            'agent'            => $agent,
        ])->filter();

        $terms = SupplierAgreedTerm::active()
            ->forOrganisation($organisation)
            ->where(function ($query) use ($levels) {
                foreach ($levels as $model) {
                    $query->orWhere(fn ($query) => $query->where('subject_type', $model->getMorphClass())->where('subject_id', $model->id));
                }
            })
            ->with(['currency', 'agreedBy', 'organisation'])
            ->get();

        return $this->resolve($terms, $levels);
    }

    /**
     * @param  Collection<int, SupplierAgreedTerm>  $terms
     * @param  Collection<string, Supplier|SupplierProduct|Agent>  $levels  closest level first
     * @return Collection<int, SupplierAgreedTerm>
     */
    public function resolve(Collection $terms, Collection $levels): Collection
    {
        $rank = $levels->keys()->flip();
        foreach ($terms as $term) {
            $term->setAttribute('level', $levels->search(fn ($model) => $model->getMorphClass() === $term->subject_type && $model->id === $term->subject_id));
        }

        $terms = $terms->sortBy(fn (SupplierAgreedTerm $term) => [$rank[$term->level], $term->organisation_id === null ? 1 : 0, -$term->id])->values();

        $typeOrder = array_flip(SupplierAgreedTermTypeEnum::values());

        return $terms
            ->filter(fn (SupplierAgreedTerm $term) => $term->term_type === SupplierAgreedTermTypeEnum::OTHER_CHARGE
                || $terms->firstWhere('term_type', $term->term_type)->is($term))
            ->sortBy(fn (SupplierAgreedTerm $term) => $typeOrder[$term->term_type->value])
            ->values();
    }
}
