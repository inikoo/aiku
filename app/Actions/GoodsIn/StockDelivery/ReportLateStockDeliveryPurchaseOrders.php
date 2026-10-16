<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\Procurement\ProcurementNote\StoreProcurementNote;
use App\Actions\Procurement\SupplierAgreedTerm\GetEffectiveSupplierAgreedTerms;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteCategoryEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteIncidentTypeEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteSeverityEnum;
use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermTypeEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\Procurement\ProcurementNote;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * When a delivery is received, each of its purchase orders is measured from the day it was submitted against the
 * lead time agreed with the supplier (or its agent). One late more than the grace days gets a Late incident on
 * the supplier, linked to the order, once (INI-104).
 */
class ReportLateStockDeliveryPurchaseOrders
{
    use AsAction;

    public const int GRACE_DAYS = 7;

    /**
     * @return Collection<int, ProcurementNote>
     */
    public function handle(StockDelivery $stockDelivery): Collection
    {
        $receivedAt = $stockDelivery->received_at ?? now();

        return $stockDelivery->purchaseOrders()->whereNotNull('submitted_at')->get()
            ->map(function (PurchaseOrder $purchaseOrder) use ($stockDelivery, $receivedAt) {
                $subject = match (true) {
                    (bool) $purchaseOrder->supplier_id => Supplier::find($purchaseOrder->supplier_id),
                    (bool) $purchaseOrder->agent_id    => Agent::find($purchaseOrder->agent_id),
                    default                            => null,
                };
                $agreedDays = $subject ? $this->agreedLeadTimeDays($subject, $stockDelivery->organisation) : null;
                if ($agreedDays === null) {
                    return null;
                }

                $days     = (int) round($purchaseOrder->submitted_at->copy()->startOfDay()->diffInDays($receivedAt->copy()->startOfDay()));
                $daysLate = $days - $agreedDays;
                if ($daysLate <= self::GRACE_DAYS || $this->alreadyReported($purchaseOrder)) {
                    return null;
                }

                return StoreProcurementNote::make()->action($subject, [
                    'title'             => __('Delivered :days days late', ['days' => $daysLate]),
                    'body'              => __('Purchase order :reference was submitted on :submitted and received on :received with delivery :delivery: :days days, against :agreed days agreed.', [
                        'reference' => $purchaseOrder->reference,
                        'submitted' => $purchaseOrder->submitted_at->toDateString(),
                        'received'  => $receivedAt->toDateString(),
                        'delivery'  => $stockDelivery->reference,
                        'days'      => $days,
                        'agreed'    => $agreedDays,
                    ]),
                    'category'          => ProcurementNoteCategoryEnum::INCIDENT->value,
                    'severity'          => ProcurementNoteSeverityEnum::WARNING->value,
                    'incident_type'     => ProcurementNoteIncidentTypeEnum::LATE->value,
                    'purchase_order_id' => $purchaseOrder->id,
                    'organisation_id'   => $stockDelivery->organisation_id,
                    'author_user_id'    => null,
                    'author_name'       => 'Aiku',
                ], strict: false);
            })
            ->filter()
            ->values();
    }

    public function agreedLeadTimeDays(Supplier|Agent $subject, Organisation $organisation): ?int
    {
        $term = GetEffectiveSupplierAgreedTerms::run($subject, $organisation)->firstWhere('term_type', SupplierAgreedTermTypeEnum::LEAD_TIME_DAYS);
        if (!$term) {
            return null;
        }
        if ($term->amount !== null) {
            return (int) $term->amount;
        }

        return preg_match('/\d+/', (string) $term->value_text, $matches) ? (int) $matches[0] : null;
    }

    private function alreadyReported(PurchaseOrder $purchaseOrder): bool
    {
        return ProcurementNote::where('purchase_order_id', $purchaseOrder->id)
            ->where('category', ProcurementNoteCategoryEnum::INCIDENT)
            ->where('incident_type', ProcurementNoteIncidentTypeEnum::LATE)
            ->exists();
    }
}
