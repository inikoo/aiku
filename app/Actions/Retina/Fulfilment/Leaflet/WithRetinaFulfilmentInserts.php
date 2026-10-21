<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\Leaflet;

use App\Enums\Catalogue\Leaflet\LeafletTypeEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Billables\ModelHasLeaflet;
use App\Rules\ImagePageSize;
use App\Rules\PdfPageSize;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

trait WithRetinaFulfilmentInserts
{
    public function authorize(ActionRequest $request): bool
    {
        if (!$request->user()->is_root || $request->user()->customer?->shop?->type !== ShopTypeEnum::FULFILMENT) {
            return false;
        }

        $customerLeaflet = $request->route('customerLeaflet');

        return !$customerLeaflet instanceof ModelHasLeaflet
            || ($customerLeaflet->model_type === 'Customer' && $customerLeaflet->model_id === $request->user()->customer_id);
    }

    /**
     * @return array<int, mixed>
     */
    protected function insertFileRules(): array
    {
        return [
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:20480',
            new PdfPageSize(105, 148, 'A6'),
            new ImagePageSize(105, 148, 'A6'),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function insertTypeRules(): array
    {
        return [Rule::enum(LeafletTypeEnum::class)->except([LeafletTypeEnum::PERSONALISED_MESSAGE])];
    }
}
