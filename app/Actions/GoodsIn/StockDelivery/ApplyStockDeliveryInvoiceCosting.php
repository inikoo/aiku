<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 28 Sep 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\GoodsIn\StockDeliveryItem\UpdateStockDeliveryItemCost;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryServiceInvoiceTypeEnum;
use App\Models\Helpers\Currency;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithStockDeliveryCostingEditAuthorisation;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryCostTypeEnum;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryStateEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryItem;
use App\Models\Helpers\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Writes the costs someone checked against the supplier invoice: each line's goods cost, the invoice
 * itself as received, and the extra charges they kept. The numbers come from the reviewer, never
 * straight from the reading. What the invoice bills that is not in the delivery is an extra expense of its own.
 *
 * An invoice in another currency than the delivery asks the reviewer whether the delivery follows the invoice:
 * yes moves the delivery to the invoice's currency, no keeps it and converts the invoice at the rate they give,
 * the lines adding up to the converted total to the cent.
 */
class ApplyStockDeliveryInvoiceCosting extends OrgAction
{
    use WithStockDeliveryCostingEditAuthorisation;

    private StockDelivery $stockDelivery;

    private Media $media;

    private ?Currency $invoiceCurrency = null;

    public function rules(): array
    {
        return [
            'invoice_number'   => ['nullable', 'string', 'max:255'],
            'invoice_date'     => ['required', 'date'],
            'invoice_total'    => ['required', 'numeric', 'gte:0'],
            'items'            => ['required', 'array'],
            'items.*.id'       => ['required', 'integer'],
            'items.*.cost'     => ['required', 'numeric', 'gte:0'],
            'charges'          => ['sometimes', 'array'],
            'charges.*.label'  => ['required', 'string', 'max:255'],
            'charges.*.amount' => ['required', 'numeric', 'gte:0'],
            'not_in_delivery'          => ['sometimes', 'array'],
            'not_in_delivery.*.label'  => ['required', 'string', 'max:255'],
            'not_in_delivery.*.amount' => ['required', 'numeric', 'gt:0'],
            'follow_invoice'   => ['sometimes', 'boolean'],
            'exchange'         => ['sometimes', 'nullable', 'numeric', 'gt:0'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if ($this->stockDelivery->state !== StockDeliveryStateEnum::PLACED || $this->stockDelivery->is_costed) {
            $validator->errors()->add('state', __('You can only edit the costs while the costing is in progress'));
        }

        $reading = ReadStockDeliveryInvoice::reading($this->stockDelivery, $this->media);

        if (Arr::get($reading, 'state') !== 'read') {
            $validator->errors()->add('invoice', __('This invoice has not been read yet'));
        } elseif (Arr::get($reading, 'applied_at')) {
            $validator->errors()->add('invoice', __('This invoice is already in the costing, change the costs in the checklist'));
        } elseif ($reading['currency'] && strtoupper($reading['currency']) !== $this->stockDelivery->currency?->code) {
            $this->validateOtherCurrency($validator, strtoupper($reading['currency']));
        }

        $ids = collect($this->get('items', []))->pluck('id');

        if ($this->stockDelivery->items()->whereIn('id', $ids)->count() !== $ids->unique()->count()) {
            $validator->errors()->add('items', __('Some lines are not in this stock delivery'));
        }
    }

    private function validateOtherCurrency(Validator $validator, string $code): void
    {
        $this->invoiceCurrency = Currency::whereRaw('upper(code) = ?', [$code])->first();

        if (!$this->invoiceCurrency) {
            $validator->errors()->add('currency', __('The invoice is in :invoice, a currency we do not know: enter its costs by hand', ['invoice' => $code]));

            return;
        }

        if (!$this->has('follow_invoice')) {
            $validator->errors()->add('currency', __('The invoice is in :invoice and the delivery in :delivery, choose whether the delivery follows the invoice', [
                'invoice'  => $code,
                'delivery' => $this->stockDelivery->currency?->code,
            ]));
        } elseif (filter_var($this->get('follow_invoice'), FILTER_VALIDATE_BOOLEAN)) {
            if ($blockers = ChangeStockDeliveryCurrency::blockers($this->stockDelivery)) {
                $validator->errors()->add('currency', __('The delivery cannot follow the invoice:').' '.implode(' ', $blockers));
            }
        } elseif (!$this->get('exchange')) {
            $validator->errors()->add('exchange', __('Enter the exchange rate from :invoice to :delivery', [
                'invoice'  => $code,
                'delivery' => $this->stockDelivery->currency?->code,
            ]));
        }
    }

    /**
     * The goods cost of each line in the delivery's currency. The cents lost to rounding go on the largest line,
     * so the lines add up to the converted total of what the reviewer entered.
     *
     * @param  array<int, array{id: int, cost: float|int|string}>  $lines
     * @return array<int, float> cost by item id
     */
    public static function convertedCosts(array $lines, float $exchange): array
    {
        $costs = [];
        foreach ($lines as $line) {
            $costs[$line['id']] = round((float) $line['cost'] * $exchange, 2);
        }

        if ($costs) {
            $largest          = array_search(max($costs), $costs);
            $costs[$largest] = round($costs[$largest] + round(array_sum(array_column($lines, 'cost')) * $exchange, 2) - array_sum($costs), 2);
        }

        return $costs;
    }

    public function handle(StockDelivery $stockDelivery, Media $media, array $modelData): StockDelivery
    {
        $invoiceCurrency = $this->invoiceCurrency;

        DB::transaction(function () use ($stockDelivery, $modelData, $invoiceCurrency) {
            StockDelivery::whereKey($stockDelivery->id)->lockForUpdate()->first();

            if ($invoiceCurrency && Arr::get($modelData, 'follow_invoice')) {
                ChangeStockDeliveryCurrency::run($stockDelivery, $invoiceCurrency);
                $invoiceCurrency = null;
            }

            $exchange          = $invoiceCurrency ? (float) $modelData['exchange'] : 1.0;
            $inInvoiceCurrency = $invoiceCurrency ? ['currency_id' => $invoiceCurrency->id, 'exchange' => $exchange] : ['currency_id' => null];
            $costs             = self::convertedCosts($modelData['items'], $exchange);
            $items             = $stockDelivery->items()->whereIn('id', array_keys($costs))->get()->keyBy('id');

            foreach ($costs as $itemId => $cost) {
                /** @var StockDeliveryItem $item */
                $item = $items->get($itemId);
                UpdateStockDeliveryItemCost::make()->action($item, ['cost_items' => $cost]);
            }

            foreach (Arr::get($modelData, 'charges', []) as $charge) {
                StoreStockDeliveryCost::make()->action($stockDelivery, [
                    'type'        => StockDeliveryCostTypeEnum::EXTRA->value,
                    'label'       => $charge['label'],
                    'amount'      => $charge['amount'],
                    'received_at' => $modelData['invoice_date'],
                    ...$inInvoiceCurrency,
                ]);
            }

            foreach (Arr::get($modelData, 'not_in_delivery', []) as $line) {
                StoreStockDeliveryCost::make()->action($stockDelivery, [
                    'type'        => StockDeliveryCostTypeEnum::EXTRA->value,
                    'category'    => StockDeliveryServiceInvoiceTypeEnum::NOT_IN_DELIVERY->value,
                    'label'       => $line['label'],
                    'amount'      => $line['amount'],
                    'received_at' => $modelData['invoice_date'],
                    ...$inInvoiceCurrency,
                ]);
            }

            $invoice     = [
                'label'       => $modelData['invoice_number'] ? __('Invoice :number', ['number' => $modelData['invoice_number']]) : null,
                'amount'      => $modelData['invoice_total'],
                'received_at' => $modelData['invoice_date'],
                'is_waiting_for_invoice' => false,
                ...$inInvoiceCurrency,
            ];
            $invoiceCost = $stockDelivery->costs()->where('type', StockDeliveryCostTypeEnum::AGENT_INVOICE)->first();

            if ($invoiceCost) {
                UpdateStockDeliveryCost::make()->action($invoiceCost, $invoice);
            } else {
                StoreStockDeliveryCost::make()->action($stockDelivery, ['type' => StockDeliveryCostTypeEnum::AGENT_INVOICE->value, ...$invoice]);
            }
        });

        ReadStockDeliveryInvoice::storeReading($stockDelivery, $media, [
            ...(ReadStockDeliveryInvoice::reading($stockDelivery, $media) ?? []),
            'applied_at' => now()->toIso8601String(),
        ]);

        return $stockDelivery->refresh();
    }

    public function asController(StockDelivery $stockDelivery, Media $media, ActionRequest $request): StockDelivery
    {
        $this->stockDelivery = $stockDelivery;
        $this->media         = $media;

        abort_unless(ReadStockDeliveryInvoice::pivot($stockDelivery, $media)->exists(), 404);

        $this->initialisation($stockDelivery->organisation, $request);

        return $this->handle($stockDelivery, $media, $this->validatedData);
    }

    public function action(StockDelivery $stockDelivery, Media $media, array $modelData): StockDelivery
    {
        $this->asAction      = true;
        $this->stockDelivery = $stockDelivery;
        $this->media         = $media;
        $this->initialisation($stockDelivery->organisation, $modelData);

        return $this->handle($stockDelivery, $media, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return redirect()->back();
    }
}
