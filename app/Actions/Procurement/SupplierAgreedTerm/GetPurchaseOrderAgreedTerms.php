<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 03:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierAgreedTerm;

use App\Enums\Procurement\ProcurementNote\ProcurementNoteVisibilityEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\SupplierAgreedTerm;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

class GetPurchaseOrderAgreedTerms
{
    use AsObject;

    /**
     * The terms in force for a purchase order: those of its supplier (or agent), then the terms
     * agreed for one of the products on its lines, each with a `product_code` attribute. On the
     * document only the terms marked to print are kept, and never an internal one: a supplier
     * reads the terms visible to suppliers, an agent those visible to agents as well.
     *
     * @return Collection<int, SupplierAgreedTerm>
     */
    public function handle(PurchaseOrder $purchaseOrder, bool $forDocument = false): Collection
    {
        return $this->forMany(collect([$purchaseOrder]), $forDocument)[$purchaseOrder->id];
    }

    /**
     * The same for several purchase orders with one read of the terms, keyed by purchase order id.
     *
     * @param  Collection<int, PurchaseOrder>  $purchaseOrders
     * @return array<int, Collection<int, SupplierAgreedTerm>>
     */
    public function forMany(Collection $purchaseOrders, bool $forDocument = false): array
    {
        $this->loadSubjects($purchaseOrders);

        $subjects = $purchaseOrders->flatMap(fn (PurchaseOrder $purchaseOrder) => $this->subjects($purchaseOrder))
            ->unique(fn ($model) => $model->getMorphClass().$model->id);

        $terms = $subjects->isEmpty() ? collect() : SupplierAgreedTerm::active()
            ->where(function ($query) use ($subjects) {
                foreach ($subjects->groupBy(fn ($model) => $model->getMorphClass()) as $type => $models) {
                    $query->orWhere(fn ($query) => $query->where('subject_type', $type)->whereIn('subject_id', $models->pluck('id')));
                }
            })
            ->with(['currency', 'agreedBy', 'organisation'])
            ->get();

        return $purchaseOrders->mapWithKeys(fn (PurchaseOrder $purchaseOrder) => [
            $purchaseOrder->id => $this->termsOf($purchaseOrder, $terms, $forDocument),
        ])->all();
    }

    public function subject(PurchaseOrder $purchaseOrder): Supplier|Agent|null
    {
        return match (true) {
            $purchaseOrder->parent instanceof OrgSupplier => $purchaseOrder->parent->supplier,
            $purchaseOrder->parent instanceof OrgAgent    => $purchaseOrder->parent->agent,
            default                                       => null,
        };
    }

    /**
     * @param  Collection<int, PurchaseOrder>  $purchaseOrders
     */
    public function loadSubjects(Collection $purchaseOrders): void
    {
        (new EloquentCollection($purchaseOrders->all()))
            ->loadMissing('purchaseOrderTransactions.supplierProduct')
            ->loadMorph('parent', [OrgSupplier::class => ['supplier.agent'], OrgAgent::class => ['agent']]);
    }

    /**
     * @return Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function subjects(PurchaseOrder $purchaseOrder): Collection
    {
        $subject = $this->subject($purchaseOrder);

        return collect([$subject, $subject instanceof Supplier ? $subject->agent : null])
            ->concat($subject instanceof Supplier ? $purchaseOrder->purchaseOrderTransactions->pluck('supplierProduct') : [])
            ->filter()
            ->values();
    }

    /**
     * @param  Collection<int, SupplierAgreedTerm>  $allTerms
     * @return Collection<int, SupplierAgreedTerm>
     */
    private function termsOf(PurchaseOrder $purchaseOrder, Collection $allTerms, bool $forDocument): Collection
    {
        $subject = $this->subject($purchaseOrder);
        if (!$subject) {
            return collect();
        }

        $allTerms = $allTerms
            ->filter(fn (SupplierAgreedTerm $term) => $term->organisation_id === null || $term->organisation_id === $purchaseOrder->organisation_id)
            ->map(fn (SupplierAgreedTerm $term) => clone $term);

        $levels = collect(['supplier' => $subject instanceof Supplier ? $subject : null, 'agent' => $subject instanceof Agent ? $subject : $subject->agent])->filter();

        $terms = GetEffectiveSupplierAgreedTerms::make()->resolve(
            $allTerms->filter(fn (SupplierAgreedTerm $term) => $levels->contains(fn ($model) => $model->getMorphClass() === $term->subject_type && $model->id === $term->subject_id))->values(),
            $levels
        );

        if ($subject instanceof Supplier) {
            $products = $purchaseOrder->purchaseOrderTransactions->pluck('supplierProduct')->filter()->pluck('code', 'id');

            $terms = $terms->concat(
                $allTerms
                    ->filter(fn (SupplierAgreedTerm $term) => $term->subject_type === 'SupplierProduct' && $products->has($term->subject_id))
                    ->sortBy(fn (SupplierAgreedTerm $term) => [$products[$term->subject_id], $term->organisation_id === null ? 1 : 0, -$term->id])
                    ->unique(fn (SupplierAgreedTerm $term) => $term->subject_id.'|'.$term->term_type->value.'|'.($term->term_type->isCharge() ? $term->value_text : ''))
                    ->each(fn (SupplierAgreedTerm $term) => $term->setAttribute('level', 'supplier_product')->setAttribute('product_code', $products[$term->subject_id]))
            );
        }

        if (!$forDocument) {
            return $terms->values();
        }

        $visibleTo = $purchaseOrder->isAgentOrder() || $subject instanceof Agent
            ? [ProcurementNoteVisibilityEnum::AGENT, ProcurementNoteVisibilityEnum::SUPPLIER]
            : [ProcurementNoteVisibilityEnum::SUPPLIER];

        return $terms
            ->filter(fn (SupplierAgreedTerm $term) => $term->show_on_po && in_array($term->visibility, $visibleTo))
            ->values();
    }
}
