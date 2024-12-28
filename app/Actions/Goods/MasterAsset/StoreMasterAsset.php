<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 27 Dec 2024 21:46:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\MasterAsset;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateMasterAssets;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Goods\MasterAsset;
use App\Models\Goods\MasterProductCategory;
use App\Models\Goods\MasterShop;
use App\Models\SysAdmin\Group;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreMasterAsset extends OrgAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Group|MasterProductCategory $parent, array $modelData): MasterAsset
    {
        data_set($modelData, 'group_id', $parent->group_id);

        if ($parent instanceof MasterProductCategory) {
            data_set($modelData, 'master_department_id', $parent->master_department_id);

            if ($parent->type == ProductCategoryTypeEnum::FAMILY) {
                data_set($modelData, 'master_family_id', $parent->id);
            }
            if ($parent->type == ProductCategoryTypeEnum::SUB_DEPARTMENT) {
                data_set($modelData, 'master_sub_department_id', $parent->id);
            }
        }

        $masterAsset = DB::transaction(function () use ($parent, $modelData) {
            /** @var MasterAsset $masterAsset */
            $masterAsset = $parent->masterAssets()->create($modelData);
            $masterAsset->stats()->create();

            return $masterAsset;
        });

        GroupHydrateMasterAssets::dispatch($parent->group)->delay($this->hydratorsDelay);

        return $masterAsset;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        $rules = [
            'code'                     => [
                'required',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'master_assets',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'                     => ['required', 'max:250', 'string'],
            'master_family_id'         => [
                'sometimes',
                'required',
                Rule::exists('master_product_categories', 'id')
                    ->where('group_id', $this->group->id)
                    ->where('type', ProductCategoryTypeEnum::FAMILY)
            ],
            'master_department_id'     => [
                'sometimes',
                'required',
                Rule::exists('master_product_categories', 'id')
                    ->where('group_id', $this->group->id)
                    ->where('type', ProductCategoryTypeEnum::DEPARTMENT)
            ],
            'master_sub_department_id' => [
                'sometimes',
                'required',
                Rule::exists('master_product_categories', 'id')
                    ->where('group_id', $this->group->id)
                    ->where('type', ProductCategoryTypeEnum::SUB_DEPARTMENT)
            ],
            'image_id'                 => ['sometimes', 'required', Rule::exists('media', 'id')->where('group_id', $this->group->id)],
            'price'                    => ['required', 'numeric', 'min:0'],
            'unit'                     => ['sometimes', 'required', 'string'],
            'rrp'                      => ['sometimes', 'required', 'numeric', 'min:0'],
            'description'              => ['sometimes', 'required', 'max:1500'],
            'data'                     => ['sometimes', 'array'],
            'is_main'                  => ['required', 'boolean'],
            'main_master_asset_id'     => [
                'sometimes',
                'nullable',
                Rule::exists('master_assets', 'id')
                    ->where('group_id', $this->group->id)
            ],
            'variant_ratio'            => ['sometimes', 'required', 'numeric', 'gt:0'],
            'variant_is_visible'       => ['sometimes', 'required', 'boolean'],

        ];

        if (!$this->strict) {
            $rules['status'] = ['sometimes', 'required', 'boolean'];
            $rules           = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(MasterShop|MasterProductCategory $parent, array $modelData, int $hydratorsDelay = 0, $strict = true, $audit = true): MasterAsset
    {
        if (!$audit) {
            MasterAsset::disableAuditing();
        }

        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;
        $this->strict         = $strict;

        $this->initialisationFromGroup($parent->group, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

}
