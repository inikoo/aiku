<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026 12:00:00 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Masters\MasterAsset;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithMastersEditAuthorisation;
use App\Enums\Catalogue\MasterProductCategory\MasterProductCategoryTypeEnum;
use App\Enums\Helpers\TimeSeries\TimeSeriesFrequencyEnum;
use App\Models\Catalogue\Product;
use App\Models\Masters\MasterAsset;
use App\Models\Masters\MasterProductCategory;
use App\Models\Masters\MasterShop;
use App\Models\Masters\MasterVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Copies a master product, and the whole variant set it belongs to, into a family of another
 * master shop. The copy is a master only: nothing is created in the shops of the target master
 * shop, which is why the master variant is built here and not through StoreMasterVariant, whose
 * job is to roll a variant out to every shop of the family.
 */
class CopyMasterAssetToMasterShop extends OrgAction
{
    use WithMastersEditAuthorisation;

    /**
     * What belongs to the source master product alone: where it sits, what it was fetched from,
     * and what is worked out from its own products in its own shops.
     */
    private const array NOT_COPIED = [
        'id',
        'slug',
        'group_id',
        'master_shop_id',
        'master_family_id',
        'master_sub_department_id',
        'master_department_id',
        'master_variant_id',
        'main_master_asset_id',
        'is_main',
        'is_variant_leader',
        'is_minion_variant',
        'index_under_master_family',
        'source_id',
        'fetched_at',
        'last_fetched_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'in_process',
        'is_single_trade_unit',
        'mismatch_detected',
        'mismatch_with_seeder_detected',
        'has_missing_child_description',
        'products_status',
        'health_rank',
        'offers_data',
        'barcode',
        'independent_barcode',
    ];

    private MasterAsset $masterAsset;

    /**
     * @throws \Throwable
     */
    public function handle(MasterAsset $masterAsset, MasterProductCategory $masterFamily): MasterAsset
    {
        $sources = $this->getMasterAssetsToCopy($masterAsset);

        return DB::transaction(function () use ($masterAsset, $masterFamily, $sources) {
            MasterShop::where('id', $masterFamily->master_shop_id)->lockForUpdate()->first();
            $blockers = $this->getBlockers($masterAsset, $masterFamily->masterShop);
            if ($blockers) {
                throw ValidationException::withMessages(['master_family_id' => $blockers]);
            }

            $copies = [];
            foreach ($sources as $source) {
                $copies[$source->id] = $this->copyMasterAsset($source, $masterFamily);
            }

            if ($masterAsset->masterVariant) {
                $this->copyMasterVariant($masterAsset->masterVariant, $masterFamily, $copies);
            }

            return $copies[$masterAsset->id]->refresh();
        });
    }

    /**
     * @return Collection<int, MasterAsset>
     */
    public function getMasterAssetsToCopy(MasterAsset $masterAsset): Collection
    {
        if (!$masterAsset->masterVariant) {
            return new Collection([$masterAsset]);
        }

        return $masterAsset->masterVariant->allProduct()->orderBy('id')->get();
    }

    /**
     * @return array{products: array<int, string>, master_shops: array<int, array{id: int, slug: string, code: string, name: string, existing_codes: array<int, string>, blockers: array<int, string>}>}
     */
    public function getDialogData(MasterAsset $masterAsset): array
    {
        $codes = $this->getMasterAssetsToCopy($masterAsset)->pluck('code');

        return [
            'products'     => $codes->all(),
            'master_shops' => MasterShop::where('group_id', $masterAsset->group_id)
                ->where('id', '!=', $masterAsset->master_shop_id)
                ->where('status', true)
                ->orderBy('code')
                ->get()
                ->map(fn (MasterShop $masterShop) => [
                    'id'             => $masterShop->id,
                    'slug'           => $masterShop->slug,
                    'code'           => $masterShop->code,
                    'name'           => $masterShop->name,
                    ...$this->inspectMasterShop($masterAsset, $masterShop),
                ])
                ->all(),
        ];
    }

    /**
     * @param array<int, string> $codes
     *
     * @return array<int, string>
     */
    private function getCodesAlreadyInMasterShop(int $masterShopId, array $codes): array
    {
        return MasterAsset::where('master_shop_id', $masterShopId)
            ->whereIn(DB::raw('lower(code)'), array_map('mb_strtolower', $codes))
            ->orderBy('code')
            ->pluck('code')
            ->all();
    }

    /**
     * Why nothing can be copied into this master shop. A product a shop already sells under one
     * of the codes counts: adding the copy to that shop would take that product over and rewrite
     * its composition, prices and texts from the master.
     *
     * @return array<int, string>
     */
    public function getBlockers(MasterAsset $masterAsset, MasterShop $masterShop): array
    {
        return $this->inspectMasterShop($masterAsset, $masterShop)['blockers'];
    }

    /**
     * @return array{existing_codes: array<int, string>, blockers: array<int, string>}
     */
    private function inspectMasterShop(MasterAsset $masterAsset, MasterShop $masterShop): array
    {
        $codes      = $this->getMasterAssetsToCopy($masterAsset)->pluck('code')->all();
        $lowerCodes = array_map('mb_strtolower', $codes);
        $blockers   = [];

        $existingCodes = $this->getCodesAlreadyInMasterShop($masterShop->id, $codes);
        if ($existingCodes) {
            $blockers[] = __('Nothing was copied: :master_shop already has :codes.', [
                'master_shop' => $masterShop->name,
                'codes'       => implode(', ', $existingCodes),
            ]);
        }

        $masterVariant = $masterAsset->masterVariant;
        if ($masterVariant && MasterVariant::where('master_shop_id', $masterShop->id)->whereRaw('lower(code) = ?', [mb_strtolower($masterVariant->code)])->exists()) {
            $blockers[] = __('Nothing was copied: :master_shop already has a variant :code.', [
                'master_shop' => $masterShop->name,
                'code'        => $masterVariant->code,
            ]);
        }

        $shopProducts = Product::query()
            ->join('shops', 'shops.id', '=', 'products.shop_id')
            ->where('shops.master_shop_id', $masterShop->id)
            ->whereIn(DB::raw('lower(products.code)'), $lowerCodes)
            ->orderBy('shops.code')
            ->orderBy('products.code')
            ->get(['shops.code as shop_code', 'products.code'])
            ->map(fn (Product $product) => $product->shop_code.' '.$product->code)
            ->all();
        if ($shopProducts) {
            $blockers[] = __('Nothing was copied: shops of :master_shop already sell products with these codes, and adding the copy to those shops would take them over. Rename or remove them first: :products.', [
                'master_shop' => $masterShop->name,
                'products'    => implode(', ', $shopProducts),
            ]);
        }

        return ['existing_codes' => $existingCodes, 'blockers' => $blockers];
    }

    /**
     * @throws \Throwable
     */
    private function copyMasterAsset(MasterAsset $source, MasterProductCategory $masterFamily): MasterAsset
    {
        $copy = StoreMasterAsset::make()->action($masterFamily, [
            'code'        => $source->code,
            'name'        => $source->name,
            'type'        => $source->type->value,
            'trade_units' => $source->tradeUnits->map(fn ($tradeUnit) => [
                'id'       => $tradeUnit->id,
                'quantity' => $tradeUnit->pivot->quantity,
            ])->all(),
        ]);

        $copy->setRawAttributes(array_merge(
            $copy->getAttributes(),
            Arr::except($source->getAttributes(), self::NOT_COPIED)
        ));
        $copy->saveQuietly();

        $this->copyImages($source, $copy);

        return $copy;
    }

    private function copyImages(MasterAsset $source, MasterAsset $copy): void
    {
        $morphClass = $source->getMorphClass();

        $images = DB::table('model_has_media')
            ->where('model_type', $morphClass)
            ->where('model_id', $source->id)
            ->orderBy('id')
            ->get()
            ->map(fn (object $image) => array_merge(Arr::except((array) $image, ['id', 'source_id', 'fetched_at', 'last_fetched_at']), ['model_id' => $copy->id]))
            ->all();

        DB::table('model_has_media')->where('model_type', $morphClass)->where('model_id', $copy->id)->delete();
        DB::table('model_has_media')->insert($images);
    }

    /**
     * @param array<int, MasterAsset> $copies keyed by the id of the master product each one was copied from
     */
    private function copyMasterVariant(MasterVariant $source, MasterProductCategory $masterFamily, array $copies): void
    {
        $products = [];
        foreach (data_get($source->data, 'products', []) as $sourceId => $entry) {
            $copy = $copies[$sourceId] ?? null;
            if (!$copy) {
                continue;
            }
            data_set($entry, 'product.id', $copy->id);
            data_set($entry, 'product.slug', $copy->slug);
            $products[$copy->id] = $entry;
        }

        $leader = $copies[$source->leader_id];

        $masterVariant = $source->replicate(['slug']);
        $masterVariant->fill([
            'master_shop_id'           => $masterFamily->master_shop_id,
            'master_family_id'         => $masterFamily->id,
            'master_sub_department_id' => $masterFamily->master_sub_department_id,
            'master_department_id'     => $masterFamily->master_department_id,
            'leader_id'                  => $leader->id,
            'number_used_slots'          => count($products),
            'number_used_slots_for_sale' => collect($copies)->where('is_for_sale', true)->count(),
            'data'                     => array_merge($source->data, ['products' => $products]),
        ]);
        $masterVariant->save();

        $masterVariant->stats()->create();
        $masterVariant->orderingStats()->create();
        foreach (TimeSeriesFrequencyEnum::cases() as $frequency) {
            $masterVariant->timeSeries()->create(['frequency' => $frequency]);
        }

        foreach ($copies as $copy) {
            $isLeader = $copy->id == $leader->id;
            $copy->updateQuietly([
                'master_variant_id' => $masterVariant->id,
                'is_main'           => $isLeader,
                'is_variant_leader' => $isLeader,
                'is_minion_variant' => !$isLeader,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'master_family_id' => [
                'required',
                'integer',
                Rule::exists('master_product_categories', 'id')
                    ->where('group_id', $this->group->id)
                    ->where('type', MasterProductCategoryTypeEnum::FAMILY)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $masterFamily = MasterProductCategory::find($this->get('master_family_id'));

        if ($masterFamily->master_shop_id == $this->masterAsset->master_shop_id) {
            $validator->errors()->add('master_family_id', __('Choose a family of another master shop: this master product is already in this one.'));

            return;
        }

        $sources = $this->getMasterAssetsToCopy($this->masterAsset);

        $masterVariant = $this->masterAsset->masterVariant;
        if ($masterVariant && !$sources->contains('id', $masterVariant->leader_id)) {
            $validator->errors()->add('master_family_id', __('The variant :code has no leader product, so it cannot be copied. Fix the variant first.', ['code' => $masterVariant->code]));

            return;
        }

        foreach ($this->getBlockers($this->masterAsset, $masterFamily->masterShop) as $blocker) {
            $validator->errors()->add('master_family_id', $blocker);
        }
    }

    /**
     * @throws \Throwable
     */
    public function action(MasterAsset $masterAsset, array $modelData): MasterAsset
    {
        $this->asAction    = true;
        $this->masterAsset = $masterAsset;
        $this->initialisationFromGroup($masterAsset->group, $modelData);

        return $this->handle($masterAsset, MasterProductCategory::find($this->validatedData['master_family_id']));
    }

    /**
     * @throws \Throwable
     */
    public function asController(MasterAsset $masterAsset, ActionRequest $request): RedirectResponse
    {
        abort_unless($masterAsset->group_id === group()->id, 404);

        $this->masterAsset = $masterAsset;
        $this->initialisationFromGroup($masterAsset->group, $request);

        $copy = $this->handle($masterAsset, MasterProductCategory::find($this->validatedData['master_family_id']));

        return redirect()
            ->route('grp.masters.master_shops.show.master_products.show', [
                'masterShop'    => $copy->masterShop->slug,
                'masterProduct' => $copy->slug,
            ])
            ->with('notification', [
                'status'      => 'success',
                'title'       => __('Success!'),
                'description' => __('Copied to :master_shop as a master product. It is not in any shop yet.', ['master_shop' => $copy->masterShop->name]),
            ])
            ->setStatusCode(303);
    }
}
