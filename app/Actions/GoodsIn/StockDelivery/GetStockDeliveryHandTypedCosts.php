<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\SupplyChain\AgentInvoice\ApplyAgentInvoiceCosting;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryCostTypeEnum;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryServiceInvoiceTypeEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryCost;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * The landed costs someone typed on a delivery, in the organisation currency, for whoever is about to add a
 * service invoice: an invoice for shipping or duty replaces the amount typed for it, and one for an extra expense
 * counts next to the one typed. The shipping an agent invoiced is not one of them, invoices add to it.
 */
class GetStockDeliveryHandTypedCosts
{
    use AsObject;

    /**
     * @return array<int, array{kind: string, kind_label: string, note: ?string, amount: float, is_estimate: bool}>
     */
    public function handle(StockDelivery $stockDelivery): array
    {
        $orgExchange     = (float) ($stockDelivery->org_exchange ?: 1);
        $hasAgentFreight = Arr::has($stockDelivery->data ?? [], ApplyAgentInvoiceCosting::FREIGHT);

        return $stockDelivery->costs()
            ->whereIn('type', [StockDeliveryCostTypeEnum::SHIPPING, StockDeliveryCostTypeEnum::DUTY, StockDeliveryCostTypeEnum::EXTRA])
            ->where('from_service_invoices', false)
            ->whereNull('stock_delivery_service_invoice_id')
            ->where('is_na', false)
            ->whereNotNull('amount')
            ->orderBy('id')
            ->get()
            ->reject(fn (StockDeliveryCost $cost) => $hasAgentFreight && $cost->type === StockDeliveryCostTypeEnum::SHIPPING)
            ->map(function (StockDeliveryCost $cost) use ($orgExchange) {
                $kind = $cost->type === StockDeliveryCostTypeEnum::EXTRA ? ($cost->category?->value ?? StockDeliveryServiceInvoiceTypeEnum::OTHER->value) : $cost->type->value;

                return [
                    'kind'        => $kind,
                    'kind_label'  => $cost->type === StockDeliveryCostTypeEnum::EXTRA
                        ? StockDeliveryServiceInvoiceTypeEnum::labels()[$kind]
                        : StockDeliveryCostTypeEnum::labels()[$kind],
                    'note'        => $cost->label,
                    'amount'      => round($cost->amountInDeliveryCurrency() * $orgExchange, 2),
                    'is_estimate' => (bool) $cost->is_estimate,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * The cost a service invoice of each type lands on: shipping, duty, its own type for an extra expense, and
     * nothing for import VAT.
     *
     * @return array<string, string|null>
     */
    public static function costKinds(): array
    {
        return collect(StockDeliveryServiceInvoiceTypeEnum::cases())
            ->mapWithKeys(fn (StockDeliveryServiceInvoiceTypeEnum $type) => [
                $type->value => match ($type->costType()) {
                    StockDeliveryCostTypeEnum::EXTRA => $type->value,
                    null                             => null,
                    default                          => $type->costType()->value,
                },
            ])
            ->all();
    }
}
