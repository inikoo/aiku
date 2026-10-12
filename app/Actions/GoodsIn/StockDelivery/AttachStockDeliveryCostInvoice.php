<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\Helpers\Media\SaveModelAttachment;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithStockDeliveryCostingEditAuthorisation;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryCostTypeEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderAttachmentScopeEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryCost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Files the invoice behind a row of the cost checklist. The agent or supplier invoice is kept on the delivery
 * itself, where its costs are read from; the invoice of any other row is kept on that row, which is created
 * still pending when the file arrives before the amount. It never changes an amount.
 */
class AttachStockDeliveryCostInvoice extends OrgAction
{
    use WithStockDeliveryCostingEditAuthorisation;

    private StockDelivery $stockDelivery;

    public function rules(): array
    {
        return [
            'type'          => ['required', Rule::enum(StockDeliveryCostTypeEnum::class)],
            'cost'          => ['sometimes', 'nullable', 'integer'],
            'attachments'   => ['required', 'array', 'min:1', 'max:10'],
            'attachments.*' => ['file', 'max:50000'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if ($this->get('cost') && !$this->stockDelivery->costs()->where('id', $this->get('cost'))->where('type', $this->get('type'))->exists()) {
            $validator->errors()->add('cost', __('This cost is not in this stock delivery'));
        }

        if ($this->get('type') === StockDeliveryCostTypeEnum::EXTRA->value && !$this->get('cost')) {
            $validator->errors()->add('cost', __('Add the extra expense before uploading its invoice'));
        }
    }

    public function handle(StockDelivery $stockDelivery, array $modelData): StockDelivery
    {
        $type  = StockDeliveryCostTypeEnum::from($modelData['type']);
        $model = $type === StockDeliveryCostTypeEnum::AGENT_INVOICE ? $stockDelivery : $this->costRow($stockDelivery, $type, $modelData['cost'] ?? null);

        foreach ($modelData['attachments'] as $file) {
            SaveModelAttachment::make()->action($model, [
                'path'         => $file->getPathName(),
                'originalName' => $file->getClientOriginalName(),
                'extension'    => $file->getClientOriginalExtension(),
                'scope'        => PurchaseOrderAttachmentScopeEnum::INVOICE->value,
                'caption'      => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            ]);
        }

        return $stockDelivery;
    }

    private function costRow(StockDelivery $stockDelivery, StockDeliveryCostTypeEnum $type, ?int $costId): StockDeliveryCost
    {
        /** @var StockDeliveryCost|null $cost */
        $cost = $costId
            ? $stockDelivery->costs()->find($costId)
            : $stockDelivery->costs()->where('type', $type)->first();

        if (!$cost && $stockDelivery->is_costed) {
            throw ValidationException::withMessages(['cost' => __('This stock delivery is costed and has no such cost to attach the invoice to')]);
        }

        return $cost ?? StoreStockDeliveryCost::make()->action($stockDelivery, ['type' => $type->value]);
    }

    public function asController(StockDelivery $stockDelivery, ActionRequest $request): StockDelivery
    {
        $this->stockDelivery = $stockDelivery;
        $this->initialisation($stockDelivery->organisation, $request);

        return $this->handle($stockDelivery, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return redirect()->back();
    }
}
