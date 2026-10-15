<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-10h-06m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Dispatching\DeliveryNote\CustomsExport;

use App\Actions\Ordering\Order\GenerateInvoiceFromOrder;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteCustomsExportFormatEnum;
use App\Models\Catalogue\Product;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Helpers\Country;
use App\Models\Helpers\TaxCategory;
use App\Models\Ordering\Transaction;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsObject;

class GetDeliveryNoteCustomsItems
{
    use AsObject;

    /**
     * @return array{formats: array<int, array{value: string, label: string}>, preview_route: array{name: string, parameters: array<string, int>}, download_route: array{name: string, parameters: array<string, int>}}|null
     */
    public function exportProp(DeliveryNote $deliveryNote, mixed $user): ?array
    {
        if (!$user instanceof User || !$deliveryNote->canBeShippedBy($user)) {
            return null;
        }

        return [
            'formats'     => DeliveryNoteCustomsExportFormatEnum::options(),
            'preview_route'  => [
                'name'       => 'grp.models.delivery_note.customs_items.preview',
                'parameters' => ['deliveryNote' => $deliveryNote->id],
            ],
            'download_route' => [
                'name'       => 'grp.models.delivery_note.customs_items.export',
                'parameters' => ['deliveryNote' => $deliveryNote->id],
            ],
        ];
    }

    /**
     * @return array{lines: array<int, array{
     *     product_code: string|null,
     *     description: string|null,
     *     tariff_code: string|null,
     *     quantity: float,
     *     pack_units: float,
     *     unit_value: float|null,
     *     currency_code: string|null,
     *     unit_weight_kg: float|null,
     *     origin_country_code: string|null,
     *     tax_paid: bool
     * }>}
     */
    public function handle(DeliveryNote $deliveryNote): array
    {
        $transactions = Transaction::query()
            ->whereIn('id', $deliveryNote->deliveryNoteItems()->whereNotNull('transaction_id')->select('transaction_id'))
            ->where('model_type', 'Product')
            ->with(['historicAsset', 'model', 'order.currency'])
            ->orderBy('id')
            ->get();

        $taxRates = TaxCategory::whereIn('id', $transactions->pluck('tax_category_id')->unique())->pluck('rate', 'id');
        $totals   = GenerateInvoiceFromOrder::make();

        $countryCodes = Country::withTrashed()
            ->whereIn('id', $transactions->map(fn (Transaction $transaction) => $transaction->model?->origin_country_id)->filter()->unique())
            ->pluck('code', 'id');

        $lines = $transactions->map(function (Transaction $transaction) use ($countryCodes, $taxRates, $totals, $deliveryNote) {
            /** @var Product|null $product */
            $product   = $transaction->model;
            $picked    = $totals->recalculateTransactionTotals($transaction, $deliveryNote);
            $quantity  = (float) $picked['quantity'];
            $netAmount = (float) $picked['net_amount'];
            $packUnits = (float) (soldPackUnits($transaction->historicAsset?->units, $product?->units) ?: 1);
            $grams     = $product?->marketing_weight ?: $product?->gross_weight;

            return [
                'product_code'        => $product?->code ?? $transaction->historicAsset?->code,
                'description'         => $transaction->historicAsset?->name ?? $product?->name,
                'tariff_code'         => $product?->tariff_code ? str_replace(' ', '', $product->tariff_code) : null,
                'quantity'            => $quantity,
                'pack_units'          => $packUnits,
                'unit_value'          => $quantity > 0 ? $netAmount / $quantity : null,
                'currency_code'       => $transaction->order?->currency?->code,
                'unit_weight_kg'      => $grams ? $grams / 1000 : null,
                'origin_country_code' => $countryCodes->get($product?->origin_country_id) ?? $this->originFromText($product?->country_of_origin),
                'tax_paid'            => $netAmount > 0 && (float) $taxRates->get($transaction->tax_category_id, 0) > 0,
            ];
        })->values()->all();

        return [
            'lines' => $lines,
        ];
    }

    private function originFromText(?string $origin): ?string
    {
        $origin = strtoupper(trim((string) $origin));

        return preg_match('/^[A-Z]{2}$/', $origin) ? $origin : null;
    }
}
