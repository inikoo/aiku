<?php

/*
 * Author Louis Perez
 * Created on 22-10-2026-18h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\ProductDevelopmentItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

/**
 * The product-specific part of the brief: fragrance, colour, look or design direction for one SKU.
 */
class UpdateProductDevelopmentItemBrief extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ProductDevelopmentItem $item;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $this->item->productDevelopment;

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->canEditProductDevelopment($request->user(), $productDevelopment);
    }

    public function handle(ProductDevelopmentItem $item, array $modelData): ProductDevelopmentItem
    {
        $old  = $item->brief;
        $item->update(['brief' => trim((string) ($modelData['brief'] ?? '')) ?: null]);

        if ($item->wasChanged('brief')) {
            RecordProductDevelopmentActivity::record($item->productDevelopment, RecordProductDevelopmentActivity::SKU_BRIEF_CHANGED, ['brief' => $old], [
                'sku'   => $item->tradeUnit->code,
                'brief' => $item->brief,
            ]);
        }

        return $item;
    }

    public function rules(): array
    {
        return [
            'brief' => ['present', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function asController(ProductDevelopmentItem $productDevelopmentItem, ActionRequest $request): ProductDevelopmentItem
    {
        $this->item = $productDevelopmentItem;
        $this->initialisationFromGroup($productDevelopmentItem->productDevelopment->group, $request);

        return $this->handle($productDevelopmentItem, $this->validatedData);
    }

    public function action(ProductDevelopmentItem $item, array $modelData): ProductDevelopmentItem
    {
        $this->asAction = true;
        $this->item     = $item;
        $this->initialisationFromGroup($item->productDevelopment->group, $modelData);

        return $this->handle($item, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
