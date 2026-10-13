<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\GoodsIn\StockDeliveryServiceInvoice\StoreStockDeliveryServiceInvoice;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithStockDeliveryCostingEditAuthorisation;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryServiceInvoiceTypeEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderAttachmentScopeEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryServiceInvoice;
use App\Models\Helpers\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

/**
 * Enters a service invoice read from a document, exactly as read: the reviewer says what each line is, never how
 * much. Lines of the same type become one service invoice, since the type decides how a cost is shared over the
 * delivery, and each keeps the document. The checks run again here, on the reading as stored.
 */
class ApplyStockDeliveryServiceInvoiceReading extends OrgAction
{
    use WithStockDeliveryCostingEditAuthorisation;

    public function rules(): array
    {
        return [
            'read_at' => ['required', 'string'],
            'types'   => ['required', 'array', 'list'],
            'types.*' => ['required', Rule::enum(StockDeliveryServiceInvoiceTypeEnum::class)],
            'checked' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * The delivery is locked while the reading is checked and entered, so the same invoice sent twice is entered once.
     *
     * @param  array{read_at: string, types: array<int, string>, checked?: bool}  $modelData
     *
     * @throws ValidationException
     */
    public function handle(StockDelivery $stockDelivery, Media $media, array $modelData): StockDelivery
    {
        DB::transaction(function () use ($stockDelivery, $media, $modelData) {
            $stockDelivery = StockDelivery::whereKey($stockDelivery->id)->lockForUpdate()->firstOrFail();
            $reading       = ReadStockDeliveryInvoice::reading($stockDelivery, $media);
            $review        = GetStockDeliveryServiceInvoiceReadings::make()->review($stockDelivery, $reading);

            $this->refuseUnlessApplicable($review, $modelData);

            $totals = [];
            foreach ($review['lines'] as $index => $line) {
                $type          = $modelData['types'][$index];
                $totals[$type] = round(($totals[$type] ?? 0) + (float) $line['amount'], 2);
            }

            $notes = __('Read from the document :file.', ['file' => $media->name ?: $media->file_name])
                .((float) $review['vat_total'] > 0 ? ' '.__('VAT :vat, to pay :total.', ['vat' => number_format((float) $review['vat_total'], 2), 'total' => number_format((float) $review['total_payable'], 2)]) : '');

            $serviceInvoiceIds = [];
            foreach ($totals as $type => $total) {
                $serviceInvoice = StoreStockDeliveryServiceInvoice::make()->action($stockDelivery->organisation, [
                    'type'         => $type,
                    'issuer'       => $review['issuer'],
                    'reference'    => $review['invoice_number'],
                    'date'         => $review['invoice_date'],
                    'currency_id'  => $review['currency_id'],
                    'total_amount' => $total,
                    'notes'        => $notes,
                    'allocations'  => [['stock_delivery_id' => $stockDelivery->id]],
                ]);

                $this->keepDocument($serviceInvoice, $media);
                $serviceInvoiceIds[] = $serviceInvoice->id;
            }

            ReadStockDeliveryInvoice::storeReading($stockDelivery, $media, [
                ...$reading,
                'applied_at'          => now()->toIso8601String(),
                'service_invoice_ids' => $serviceInvoiceIds,
            ]);
        });

        return $stockDelivery->refresh();
    }

    /**
     * @param  array<string, mixed>|null  $review
     * @param  array{read_at: string, types: array<int, string>, checked?: bool}  $modelData
     *
     * @throws ValidationException
     */
    private function refuseUnlessApplicable(?array $review, array $modelData): void
    {
        $refusal = match (true) {
            data_get($review, 'state') !== 'read' || !array_key_exists('problems', $review) => ['invoice' => __('This invoice has not been read yet')],
            (bool) ($review['applied_at'] ?? null)                                           => ['invoice' => __('This invoice is already in the service invoices')],
            $review['read_at'] !== $modelData['read_at']                                     => ['invoice' => __('This invoice was read again since you opened it, review it again')],
            (bool) $review['problems']                                                       => ['invoice' => implode(' ', $review['problems'])],
            $review['needs_manual_check'] && empty($modelData['checked'])                    => ['checked' => __('This document has no text to check the reading against: confirm you have checked every amount')],
            count($modelData['types']) !== count($review['lines'])                           => ['types' => __('Say what every line of the invoice is')],
            default                                                                          => null,
        };

        if ($refusal) {
            throw ValidationException::withMessages($refusal);
        }
    }

    private function keepDocument(StockDeliveryServiceInvoice $serviceInvoice, Media $media): void
    {
        $serviceInvoice->attachments()->attach([
            $media->id => [
                'group_id' => $serviceInvoice->group_id,
                'scope'    => PurchaseOrderAttachmentScopeEnum::INVOICE->value,
                'caption'  => $media->name ?: pathinfo($media->file_name, PATHINFO_FILENAME),
                'data'     => '{}',
            ],
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function asController(StockDelivery $stockDelivery, Media $media, ActionRequest $request): StockDelivery
    {
        abort_if($stockDelivery->organisation->type === OrganisationTypeEnum::AGENT, 403);
        abort_unless(ReadStockDeliveryInvoice::pivot($stockDelivery, $media)->where('scope', PurchaseOrderAttachmentScopeEnum::SERVICE_INVOICE->value)->exists(), 404);

        $this->initialisation($stockDelivery->organisation, $request);

        return $this->handle($stockDelivery, $media, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return redirect()->back();
    }
}
