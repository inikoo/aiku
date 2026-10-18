<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-15 16:34
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Helpers\Media;

use App\Actions\GoodsIn\StockDelivery\ReadStockDeliveryInvoice;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithAttachmentEditAuthorisation;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderAttachmentScopeEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\Helpers\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class UpdateModelAttachment extends OrgAction
{
    use WithAttachmentEditAuthorisation;

    private StockDelivery $model;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $this->canChangeAttachments($request->user(), $this->model);
    }

    public function handle(StockDelivery $model, Media $attachment, array $modelData): void
    {
        $currentAttachment = $model->attachments()->where('media.id', $attachment->id)->first();
        abort_unless($currentAttachment, 404);

        $pivotData = [];

        if (Arr::has($modelData, 'caption')) {
            $pivotData['caption'] = trim(Arr::get($modelData, 'caption'));
        }

        if (Arr::has($modelData, 'scope') && Arr::get($modelData, 'scope') !== $currentAttachment->pivot->scope) {
            if (Arr::get(ReadStockDeliveryInvoice::reading($model, $attachment), 'applied_at')) {
                throw ValidationException::withMessages([
                    'scope' => __('This document is already used in the costing, its type cannot be changed'),
                ]);
            }

            $pivotData['scope'] = Arr::get($modelData, 'scope');
        }

        if ($pivotData) {
            $model->attachments()->updateExistingPivot($attachment->id, $pivotData);
        }
    }

    public function rules(): array
    {
        return [
            'caption' => ['sometimes', 'required', 'string', 'max:255'],
            'scope'   => ['sometimes', 'required', 'string', Rule::in(array_column(PurchaseOrderAttachmentScopeEnum::options(), 'code'))],
        ];
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }

    public function action(StockDelivery $model, Media $attachment, array $modelData): void
    {
        $this->asAction = true;
        $this->model    = $model;
        $this->initialisation($model->organisation, $modelData);

        $this->handle($model, $attachment, $this->validatedData);
    }

    public function inStockDelivery(StockDelivery $stockDelivery, Media $attachment, ActionRequest $request): void
    {
        $this->model = $stockDelivery;
        $this->initialisation($stockDelivery->organisation, $request);

        $this->handle($stockDelivery, $attachment, $this->validatedData);
    }
}
