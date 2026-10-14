<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 13:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierAgreedTerm;

use App\Actions\SupplyChain\AgentInvoice\UpdateAgentInvoiceCharges;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermTypeEnum;
use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermValueTypeEnum;
use App\Enums\SupplyChain\StockDeliveryInvoice\StockDeliveryInvoiceSourceEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\Procurement\SupplierAgreedTerm;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\AgentInvoice;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierInvoice;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * What on a purchase order or on a delivery's invoice differs from what was agreed with the supplier or agent.
 * Nobody but purchasing staff can accept a difference, every one of them, with a reason: until then the purchase
 * order is not submitted and the delivery is not paid. Each difference has a key made of what it found, so an
 * accepted difference stays accepted only while the document says the same thing. A supplier or agent with no
 * agreed terms recorded yet is not checked: its terms get entered first.
 */
class CheckAgainstAgreedTerms
{
    use AsObject;

    public const string ACCEPTED = 'agreed_terms_accepted';

    /**
     * Incoterms where the seller pays the freight to the destination port.
     */
    private const array SELLER_PAYS_FREIGHT = ['CFR', 'CIF', 'CPT', 'CIP', 'DAP', 'DPU', 'DDP'];

    /**
     * @return array<int, array{key: string, title: string, found: string, agreed: string, amount: float|null, currency_id: int|null, accepted: array<string, mixed>|null}>
     */
    public function purchaseOrder(PurchaseOrder $purchaseOrder): array
    {
        $terms = GetPurchaseOrderAgreedTerms::run($purchaseOrder);
        if ($terms->isEmpty()) {
            return [];
        }

        $purchaseOrder->loadMissing('currency', 'purchaseOrderTransactions.supplierProduct.currency');

        $flags = [];

        $paymentCurrency = $terms->first(fn (SupplierAgreedTerm $term) => $term->term_type === SupplierAgreedTermTypeEnum::PAYMENT_CURRENCY && !isset($term->product_code));
        if ($paymentCurrency && !$this->namesCurrency($paymentCurrency, $purchaseOrder->currency->code)) {
            $flags[] = $this->flag(__('Payment currency'), $purchaseOrder->currency->code, $paymentCurrency->valueLabel());
        }

        $purchaseOrder->purchaseOrderTransactions
            ->filter(fn (PurchaseOrderTransaction $transaction) => $transaction->state !== PurchaseOrderTransactionStateEnum::CANCELLED
                && $transaction->supplierProduct
                && $transaction->supplierProduct->currency_id === $purchaseOrder->currency_id
                && round((float) $transaction->unit_cost, 4) > round((float) $transaction->supplierProduct->cost, 4))
            ->each(function (PurchaseOrderTransaction $transaction) use (&$flags, $purchaseOrder) {
                $code    = $purchaseOrder->currency->code;
                $flags[] = $this->flag(
                    __('Unit cost of :code', ['code' => $transaction->supplierProduct->code]),
                    $code.' '.$this->amount($transaction->unit_cost),
                    $code.' '.$this->amount($transaction->supplierProduct->cost),
                    round(((float) $transaction->unit_cost - (float) $transaction->supplierProduct->cost) * (float) $transaction->quantity_ordered, 2),
                    $purchaseOrder->currency_id
                );
            });

        return $this->withAccepted($flags, $purchaseOrder->data);
    }

    /**
     * The charges on the invoice the supplier or agent sent for a delivery. An estimate is ours, not theirs, and
     * is not checked.
     *
     * @return array<int, array{key: string, title: string, found: string, agreed: string, amount: float|null, currency_id: int|null, accepted: array<string, mixed>|null}>
     */
    public function stockDelivery(StockDelivery $stockDelivery): array
    {
        /** @var AgentInvoice|SupplierInvoice|null $invoice */
        $invoice = $stockDelivery->agent_id ? $stockDelivery->agentInvoice()->first() : $stockDelivery->supplierInvoice()->first();
        $subject = $this->subjectOf($stockDelivery);

        if (!$invoice || !$subject || $invoice->source === StockDeliveryInvoiceSourceEnum::ESTIMATED || empty($invoice->charges)) {
            return [];
        }

        $invoice->loadMissing('currency');
        $terms = GetEffectiveSupplierAgreedTerms::run($subject, $stockDelivery->organisation);
        if ($terms->isEmpty()) {
            return [];
        }

        $flags = [];
        foreach ($invoice->charges as $charge) {
            $agreed = $this->chargeBreaksTerms($charge, $terms, $invoice);
            if ($agreed !== null) {
                $flags[] = $this->flag(
                    trim(Arr::get($charge, 'description')),
                    $invoice->currency->code.' '.$this->amount($charge['amount']),
                    $agreed,
                    (float) $charge['amount'],
                    $invoice->currency_id
                );
            }
        }

        return $this->withAccepted($flags, $stockDelivery->data);
    }

    /**
     * The differences for the page of a purchase order or delivery, or null when there are none.
     *
     * @return array{flags: array<int, array<string, mixed>>, route: array{name: string, parameters: array<int, int>}}|null
     */
    public function forPage(PurchaseOrder|StockDelivery $document): ?array
    {
        $flags = $document instanceof PurchaseOrder ? $this->purchaseOrder($document) : $this->stockDelivery($document);

        return $flags ? [
            'flags' => $flags,
            'route' => [
                'name'       => $document instanceof PurchaseOrder ? 'grp.models.purchase-order.agreed_terms.accept' : 'grp.models.stock-delivery.agreed_terms.accept',
                'parameters' => [$document->id],
            ],
        ] : null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $flags
     */
    public static function hasUnaccepted(array $flags): bool
    {
        return collect($flags)->contains(fn (array $flag) => !$flag['accepted']);
    }

    public function subjectOf(StockDelivery $stockDelivery): Supplier|Agent|null
    {
        return match (true) {
            (bool) $stockDelivery->agent_id            => $stockDelivery->agent,
            $stockDelivery->parent instanceof OrgSupplier => $stockDelivery->parent->supplier,
            default                                    => $stockDelivery->supplier_id ? Supplier::find($stockDelivery->supplier_id) : null,
        };
    }

    /**
     * What was agreed for a charge the document breaks, or null when the charge keeps to the terms.
     *
     * @param  array{description: string, type?: string, amount: float|string}  $charge
     * @param  Collection<int, SupplierAgreedTerm>  $terms
     */
    private function chargeBreaksTerms(array $charge, Collection $terms, AgentInvoice|SupplierInvoice $invoice): ?string
    {
        $amount = (float) $charge['amount'];
        $type   = $charge['type'] ?? UpdateAgentInvoiceCharges::CHARGE_OTHER;

        if ($amount <= 0) {
            return null;
        }

        if ($type === UpdateAgentInvoiceCharges::CHARGE_FREIGHT) {
            $incoterm = $terms->firstWhere('term_type', SupplierAgreedTermTypeEnum::INCOTERM);
            $rule     = strtoupper(strtok(trim((string) $incoterm?->value_text), ' ') ?: '');

            return in_array($rule, self::SELLER_PAYS_FREIGHT, true)
                ? __('Incoterm :incoterm: the freight is theirs to pay', ['incoterm' => $incoterm->value_text])
                : null;
        }

        $termType = SupplierAgreedTermTypeEnum::tryFrom($type);
        $term     = $termType && $termType !== SupplierAgreedTermTypeEnum::OTHER_CHARGE
            ? $terms->firstWhere('term_type', $termType)
            : $terms->first(fn (SupplierAgreedTerm $term) => $term->term_type === SupplierAgreedTermTypeEnum::OTHER_CHARGE
                && str_contains(mb_strtolower($charge['description']), mb_strtolower(trim((string) $term->value_text))));

        if (!$term) {
            return __('Not agreed');
        }

        $allowed = match ($term->value_type) {
            SupplierAgreedTermValueTypeEnum::INCLUDED, SupplierAgreedTermValueTypeEnum::NOT_ALLOWED => 0.0,
            SupplierAgreedTermValueTypeEnum::FIXED_AMOUNT, SupplierAgreedTermValueTypeEnum::PER_ORDER => $term->currency_id === $invoice->currency_id ? (float) $term->amount : null,
            SupplierAgreedTermValueTypeEnum::PER_UNIT => $term->currency_id === $invoice->currency_id ? (float) $term->amount * collect($invoice->lines)->sum('quantity') : null,
            SupplierAgreedTermValueTypeEnum::PERCENTAGE => (float) $term->amount * (float) $invoice->goods_amount / 100,
            // ponytail: per carton and free text can't be priced from the invoice, they're left to the buyer's eye
            default => INF,
        };

        if ($allowed === null) {
            return $term->valueLabel().' '.__('(agreed in another currency)');
        }

        return round($amount, 2) > round($allowed, 2) ? $term->valueLabel() : null;
    }

    private function namesCurrency(SupplierAgreedTerm $term, string $code): bool
    {
        return $term->currency?->code === $code || str_contains(strtoupper((string) $term->value_text), $code);
    }

    /**
     * @return array{key: string, title: string, found: string, agreed: string, amount: float|null, currency_id: int|null, accepted: null}
     */
    private function flag(string $title, string $found, string $agreed, ?float $amount = null, ?int $currencyId = null): array
    {
        return [
            'key'         => substr(md5($title.'|'.$found), 0, 16),
            'title'       => $title,
            'found'       => $found,
            'agreed'      => $agreed,
            'amount'      => $amount,
            'currency_id' => $currencyId,
            'accepted'    => null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $flags
     */
    private function withAccepted(array $flags, ?array $data): array
    {
        $accepted = Arr::get($data ?? [], self::ACCEPTED, []);

        return array_map(fn (array $flag) => ['accepted' => $accepted[$flag['key']] ?? null] + $flag, $flags);
    }

    private function amount(float|string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
