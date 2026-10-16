<?php

/*
 * Author: Rifqi <rifqitaufiqurrohman1@gmail.com>
 * Created: Fri, 16 Oct 2026
 * Copyright (c) 2026
 */

namespace App\Actions\Masters\MasterAsset;

use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithMastersEditAuthorisation;
use App\Models\Goods\TradeUnit;
use App\Models\Masters\MasterAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Saves the "Edit product" page of a master product. Each value goes where it is kept: the texts,
 * price and barcode on the master product, the physical data on its trade unit (the master reads
 * them from it), and the shipping and return texts on its master family for all its products.
 */
class UpdateMasterProductInformation extends OrgAction
{
    use WithMastersEditAuthorisation;

    private MasterAsset $masterAsset;

    public function handle(MasterAsset $masterAsset, array $modelData): MasterAsset
    {
        $masterData = Arr::only($modelData, ['code', 'name', 'price', 'description', 'description_extra', 'barcode']);
        if ($masterData !== []) {
            UpdateMasterAsset::make()->action($masterAsset, $masterData);
        }

        $tradeUnitData = Arr::only($modelData, ['marketing_weight', 'gross_weight', 'marketing_ingredients', 'origin_country_id']);
        if (Arr::has($modelData, 'dimensions_cm')) {
            $tradeUnitData['marketing_dimensions'] = [
                'type'  => 'rectangular',
                'units' => 'cm',
                ...collect(Arr::get($modelData, 'dimensions_cm'))->map(fn ($centimetres) => round((float)$centimetres / 100, 4))->all(),
            ];
        }

        $tradeUnit = $masterAsset->tradeUnits()->count() === 1 ? $masterAsset->tradeUnits()->first() : null;
        if ($tradeUnitData !== [] && $tradeUnit) {
            $tradeUnit = UpdateTradeUnit::make()->action($tradeUnit, $tradeUnitData);
            $this->syncFromTradeUnit($masterAsset, $tradeUnit);
        }

        $masterFamily = $masterAsset->masterFamily;
        if (Arr::has($modelData, 'shared') && $masterFamily) {
            $masterFamily->update([
                'data' => [
                    ...($masterFamily->data ?? []),
                    'shared_information' => [
                        'shipping'      => Arr::get($modelData, 'shared.shipping'),
                        'return_policy' => Arr::get($modelData, 'shared.return_policy'),
                    ],
                ],
            ]);
        }

        return $masterAsset->refresh();
    }

    /**
     * UpdateTradeUnit leaves dimensions and origin off the master and queues the weights, so the
     * page would show the old values right after saving: the master takes them from its only trade
     * unit here.
     */
    private function syncFromTradeUnit(MasterAsset $masterAsset, TradeUnit $tradeUnit): void
    {
        $masterAsset->updateQuietly([
            'marketing_weight'     => $tradeUnit->marketing_weight,
            'gross_weight'         => $tradeUnit->gross_weight,
            'marketing_dimensions' => $tradeUnit->marketing_dimensions,
            'origin_country_id'    => $tradeUnit->origin_country_id,
        ]);
    }

    public function rules(): array
    {
        return [
            'code'                  => ['sometimes', 'required', 'string', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
            'name'                  => ['sometimes', 'required', 'string', 'max:250'],
            'price'                 => ['sometimes', 'required', 'numeric', 'gt:0'],
            'description'           => ['sometimes', 'required', 'string', 'max:1500'],
            'description_extra'     => ['sometimes', 'nullable', 'string', 'max:65500'],
            'barcode'               => ['sometimes', 'nullable', 'regex:/^(\d{8}|\d{12}|\d{13})$/'],
            'marketing_weight'      => ['sometimes', 'nullable', 'integer', 'min:1'],
            'gross_weight'          => ['sometimes', 'nullable', 'integer', 'min:1'],
            'dimensions_cm'         => ['sometimes', 'array:l,w,h'],
            'dimensions_cm.*'       => ['required', 'numeric', 'min:1'],
            'marketing_ingredients' => ['sometimes', 'nullable', 'string'],
            'origin_country_id'     => ['sometimes', 'nullable', 'exists:countries,id'],
            'shared'                => ['sometimes', 'array:shipping,return_policy'],
            'shared.*'              => ['nullable', 'string', 'max:65500'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'code.regex'          => __('Letters, numbers and hyphens only'),
            'price.gt'            => __('The price must be more than 0'),
            'barcode.regex'       => __('A barcode has 8, 12 or 13 digits'),
            'dimensions_cm.*.min' => __('Each dimension is at least 1 cm'),
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $data = $validator->getData();

        $netWeight      = Arr::get($data, 'marketing_weight', $this->masterAsset->marketing_weight);
        $shippingWeight = Arr::get($data, 'gross_weight', $this->masterAsset->gross_weight);
        if ($netWeight && $shippingWeight && $shippingWeight < $netWeight) {
            $validator->errors()->add('gross_weight', __('The shipping weight cannot be below the net weight'));
        }

        $hasTradeUnitData = Arr::hasAny($data, ['marketing_weight', 'gross_weight', 'dimensions_cm', 'marketing_ingredients', 'origin_country_id']);
        if ($hasTradeUnitData && $this->masterAsset->tradeUnits()->count() !== 1) {
            $validator->errors()->add('marketing_weight', __('This product is made of several trade units: edit their data in each trade unit'));
        }
    }

    public function asController(MasterAsset $masterAsset, ActionRequest $request): MasterAsset
    {
        $this->masterAsset = $masterAsset;
        $this->initialisationFromGroup($masterAsset->group, $request);

        return $this->handle($masterAsset, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
