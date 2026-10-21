<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Dec 2025 12:24:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Actions\Masters\MasterAsset\Hydrators\MasterAssetHydrateTagsFromTradeUnits;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateMarketingIngredientsFromTradeUnits;
use App\Actions\Masters\MasterAsset\Hydrators\MasterAssetHydrateEffectiveCost;
use App\Actions\Masters\MasterAsset\Hydrators\MasterAssetHydrateGrossWeightFromTradeUnits;
use App\Actions\Masters\MasterAsset\Hydrators\MasterAssetHydrateHealthAndSafetyFromTradeUnits;
use App\Actions\Masters\MasterAsset\Hydrators\MasterAssetHydrateLabelInfoFromTradeUnits;
use App\Actions\Masters\MasterAsset\Hydrators\MasterAssetHydrateMarketingWeightFromTradeUnits;
use App\Models\Catalogue\Product;
use App\Models\Goods\TradeUnit;
use App\Models\Masters\MasterAsset;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

trait WithMasterAssetTradeUnits
{
    public const int MAX_COMPONENT_TRADE_UNITS = 3;

    /**
     * How the composition editor should offer units. A single trade unit states its own pack
     * size, so there is nothing to show. Several that disagree cannot imply one, so units are
     * typed by hand with nothing to opt out of. Several that agree do imply one, and that is
     * the only case where opting out is a choice.
     *
     * @return array{shown: bool, canToggle: bool, byHand: bool}
     */
    public function getUnitsEditability(MasterAsset|Product $model): array
    {
        $tradeUnits = $model->tradeUnits;

        /** One trade unit states its own pack size, unless somebody set the units by hand: then the switch back must be reachable. */
        if ($tradeUnits->count() < 2) {
            return $model->has_independent_units
                ? ['shown' => true, 'canToggle' => true, 'byHand' => true]
                : ['shown' => false, 'canToggle' => false, 'byHand' => false];
        }

        $impliedUnits = $this->getSharedTradeUnitQuantity(
            $tradeUnits->map(fn ($tradeUnit) => ['quantity' => $tradeUnit->pivot->quantity])->all()
        );

        if ($impliedUnits === null) {
            return ['shown' => true, 'canToggle' => false, 'byHand' => true];
        }

        return ['shown' => true, 'canToggle' => true, 'byHand' => (bool) $model->has_independent_units];
    }

    /**
     * The units a composition implies: the trade unit quantity when every trade unit packs the
     * same number of them, null when they differ and no single pack size can be read off it.
     * Past MAX_COMPONENT_TRADE_UNITS it is an assortment: a starter pack of 18 soaps at 6 each
     * is "6 of each design", not a pack of 6, so it implies no units either.
     *
     * @param array<int, array{quantity?: mixed}> $tradeUnits
     */
    public function getSharedTradeUnitQuantity(array $tradeUnits): ?float
    {
        if (count($tradeUnits) > self::MAX_COMPONENT_TRADE_UNITS) {
            return null;
        }

        $quantities = array_unique(
            array_map(fn ($tradeUnit) => (float) Arr::get($tradeUnit, 'quantity', 1), array_values($tradeUnits))
        );

        if (count($quantities) !== 1) {
            return null;
        }

        $quantity = reset($quantities);

        return $quantity == 1 && count($tradeUnits) > 1 ? null : $quantity;
    }

    /**
     * @return array{type: string, label: string, value: float, canToggle: bool, hasOther: array{name: string, value: bool}}|null
     */
    public function getUnitsField(MasterAsset|Product $model, ?array $saveConfirmation = null): ?array
    {
        $editability = $this->getUnitsEditability($model);

        if (!$editability['shown']) {
            return null;
        }

        return [
            'type'             => 'composition_units',
            'label'            => __('Units'),
            'value'            => (float) $model->units,
            'canToggle'        => $editability['canToggle'],
            /** The block owns its save button, so it can hide it while there is nothing to save. */
            'noSaveButton'     => true,
            'hasOther'         => ['name' => 'has_independent_units', 'value' => $editability['byHand']],
            'saveConfirmation' => $saveConfirmation,
        ];
    }

    /**
     * Switching units back to "use the composition" posts only the flag, so the units are read
     * off the composition already saved. Null when it implies none and the units stay as they are.
     */
    public function getUnitsFromCurrentTradeUnits(MasterAsset|Product $model): ?float
    {
        if ($model->tradeUnits->isEmpty()) {
            return null;
        }

        return $this->getUnitsFromTradeUnits(
            $model->tradeUnits->map(fn ($tradeUnit) => ['quantity' => $tradeUnit->pivot->quantity, 'type' => $tradeUnit->type])->all()
        )['units'];
    }

    /**
     * Units are null when the composition cannot imply them - trade units packing different
     * quantities - and the caller leaves whatever was set by hand alone rather than flattening
     * it to 1.
     *
     * @param array<int, array{quantity?: mixed, type?: mixed}> $tradeUnits
     *
     * @return array{units: float|null, unit: string|null}
     */
    public function getUnitsFromTradeUnits(array $tradeUnits): array
    {
        if (empty($tradeUnits)) {
            return ['units' => 1.0, 'unit' => null];
        }

        $units = $this->getSharedTradeUnitQuantity($tradeUnits);

        if (count($tradeUnits) === 1) {
            return ['units' => $units, 'unit' => Arr::get(array_values($tradeUnits)[0], 'type')];
        }

        return ['units' => $units, 'unit' => 'bundle'];
    }

    /**
     * @param array<int, array{id?: mixed, quantity?: mixed}> $tradeUnits
     */
    public function validateTradeUnitQuantities(Validator $validator, array $tradeUnits): void
    {
        $fractional = collect($tradeUnits)->filter(fn (array $tradeUnit) => fmod(round((float) Arr::get($tradeUnit, 'quantity', 1), 3), 1.0) !== 0.0);

        if ($fractional->isEmpty()) {
            return;
        }

        TradeUnit::whereIn('id', $fractional->pluck('id'))
            ->where('is_divisible', false)
            ->pluck('code')
            ->each(fn (string $code) => $validator->errors()->add(
                'trade_units',
                __(':code is sold in whole units only. Mark the trade unit as divisible to use part of it.', ['code' => $code])
            ));
    }

    public function processTradeUnits(MasterAsset $masterAsset, array $tradeUnitsRaw): void
    {
        $stocks     = [];
        $tradeUnits = [];
        foreach ($tradeUnitsRaw as $item) {
            $tradeUnit                  = TradeUnit::find(Arr::get($item, 'id'));
            $tradeUnits[$tradeUnit->id] = [
                'quantity' => wholeTradeUnitQuantity(Arr::get($item, 'quantity'))
            ];

            foreach ($tradeUnit->stocks as $stock) {
                $stocks[$stock->id] = [
                    'quantity' => Arr::get($item, 'quantity') / $stock->pivot->quantity,
                ];
            }
        }

        $masterAsset->tradeUnits()->sync($tradeUnits);
        $masterAsset->stocks()->sync($stocks);
        ModelHydrateSingleTradeUnits::run($masterAsset);
        MasterAssetHydrateHealthAndSafetyFromTradeUnits::run($masterAsset);
        MasterAssetHydrateLabelInfoFromTradeUnits::run($masterAsset);
        MasterAssetHydrateTagsFromTradeUnits::run($masterAsset);
        MasterAssetHydrateMarketingWeightFromTradeUnits::run($masterAsset->id);
        MasterAssetHydrateGrossWeightFromTradeUnits::run($masterAsset->id);
        ProductHydrateMarketingIngredientsFromTradeUnits::run($masterAsset);
        MasterAssetHydrateEffectiveCost::dispatch($masterAsset)->afterCommit();

        $masterAsset->refresh();
    }
}
