<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 11:35
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\GoodsIn\StockDeliveryPackingCheck;

use App\Actions\OrgAction;
use App\Actions\SupplyChain\PackingSpec\ReturnPackingSpec;
use App\Actions\SupplyChain\PackingSpec\StorePackingSpec;
use App\Actions\SupplyChain\PackingSpec\VerifyPackingSpec;
use App\Actions\Traits\Authorisations\WithGoodsInBookInAuthorisation;
use App\Enums\SupplyChain\PackingSpec\PackingCheckResultEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecFateEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecStatusEnum;
use App\Models\GoodsIn\StockDeliveryPackingCheck;
use App\Models\SupplyChain\PackingSpecLine;
use App\Models\SupplyChain\SupplierProductPackingSpec;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Goods in enters what it weighed: the gross weight of the sampled cartons, and on the first delivery of a spec version
 * the grams of each packing material and what the warehouse does with it. Measured weights replace the declared ones
 * on the spec. A carton or a material set off by more than the tolerance sends the spec back to the agent; a match on a
 * delivery whose materials were weighed verifies it.
 */
class RecordStockDeliveryPackingCheck extends OrgAction
{
    use WithGoodsInBookInAuthorisation;

    public const float MIN_PACKING_GRAMS_TO_COMPARE = 50.0;

    private StockDeliveryPackingCheck $check;

    public function handle(StockDeliveryPackingCheck $check, array $modelData, ?User $user = null): StockDeliveryPackingCheck
    {
        return DB::transaction(function () use ($check, $modelData, $user) {
            $spec          = $check->packingSpec()->with(['lines', 'supplierProduct'])->lockForUpdate()->first();
            $declaredGrams = $spec->packagingGramsPerCarton();

            foreach (Arr::get($modelData, 'lines', []) as $row) {
                /** @var PackingSpecLine|null $line */
                $line = $spec->lines->firstWhere('id', $row['id']);
                if (!$line) {
                    continue;
                }
                $changes = ['fate_at_our_warehouse' => $row['fate_at_our_warehouse'] ?? $line->fate_at_our_warehouse];
                if ($check->weigh_materials && isset($row['weight_g_each'])) {
                    $changes['weight_g_each']      = $row['weight_g_each'];
                    $changes['weight_is_measured'] = true;
                }
                $line->update($changes);
            }

            $spec->unsetRelation('lines');
            $measuredGrams = $spec->packagingGramsPerCarton();

            $cartonWeights = array_values(array_map('floatval', Arr::get($modelData, 'carton_weights_g', [])));
            $average       = $cartonWeights ? array_sum($cartonWeights) / count($cartonWeights) : null;
            $expected      = $spec->expectedCartonGrossWeightG();
            $deviation     = $average && $expected ? round(($average - $expected) / $expected * 100, 2) : null;

            $problems = [];
            if ($deviation !== null && abs($deviation) > SupplierProductPackingSpec::WEIGHT_TOLERANCE_PCT) {
                $problems[] = __('Cartons weigh :average kg on average, the spec says :expected kg (:deviation%).', [
                    'average'   => round($average / 1000, 2),
                    'expected'  => round($expected / 1000, 2),
                    'deviation' => ($deviation > 0 ? '+' : '').$deviation,
                ]);
            }
            if ($check->weigh_materials && $declaredGrams >= self::MIN_PACKING_GRAMS_TO_COMPARE) {
                $materialsDeviation = round(($measuredGrams - $declaredGrams) / $declaredGrams * 100, 2);
                if (abs($materialsDeviation) > SupplierProductPackingSpec::WEIGHT_TOLERANCE_PCT) {
                    $problems[] = __('Packing materials weigh :measured g per carton, the agent declared :declared g (:deviation%).', [
                        'measured'  => round($measuredGrams),
                        'declared'  => round($declaredGrams),
                        'deviation' => ($materialsDeviation > 0 ? '+' : '').$materialsDeviation,
                    ]);
                }
            }

            $check->update([
                'carton_weights_g'   => $cartonWeights,
                'deviation_pct'      => $deviation,
                'result'             => $problems ? PackingCheckResultEnum::MISMATCH : PackingCheckResultEnum::PASSED,
                'checked_by_user_id' => $user?->id,
                'checked_at'         => now(),
            ]);

            $spec->update(array_filter([
                'measured_carton_gross_weight_g' => $average ? (int)round($average) : null,
                'materials_weighed'              => $check->weigh_materials ?: null,
            ], fn ($value) => $value !== null));
            StorePackingSpec::make()->weightCheck($spec);

            if (!in_array($spec->status, PackingSpecStatusEnum::live())) {
                return $check;
            }

            if ($problems) {
                ReturnPackingSpec::make()->handle($spec, __('Goods in check of delivery :reference', ['reference' => $check->stockDelivery->reference])."\n".implode("\n", $problems), $user);
            } elseif ($spec->status === PackingSpecStatusEnum::SUBMITTED && $spec->materials_weighed && !$spec->needs_review) {
                VerifyPackingSpec::make()->handle($spec, $user);
            }

            return $check;
        });
    }

    public function rules(): array
    {
        return [
            'carton_weights_g'               => ['required', 'array', 'min:1'],
            'carton_weights_g.*'             => ['required', 'numeric', 'gt:0'],
            'lines'                          => ['sometimes', 'array'],
            'lines.*.id'                     => ['required', 'integer'],
            'lines.*.weight_g_each'          => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'lines.*.fate_at_our_warehouse'  => ['sometimes', Rule::enum(PackingSpecFateEnum::class)],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->check->weigh_materials) {
            return;
        }

        $lineIds = $this->check->packingSpec->lines()->pluck('id')->all();
        $weighed = collect($this->get('lines', []))->filter(fn (array $line) => Arr::get($line, 'weight_g_each') !== null)->pluck('id')->all();
        if (array_diff($lineIds, $weighed)) {
            $validator->errors()->add('lines', __('Weigh every packing material of this product'));
        }
    }

    public function asController(StockDeliveryPackingCheck $stockDeliveryPackingCheck, ActionRequest $request): RedirectResponse
    {
        $this->check = $stockDeliveryPackingCheck;
        $this->initialisation($stockDeliveryPackingCheck->organisation, $request);
        $this->handle($stockDeliveryPackingCheck, $this->validatedData, $request->user());

        return redirect()->back();
    }
}
