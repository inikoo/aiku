<?php

/*
 * author Arya Permana - Kirin
 * created on 14-10-2024-13h-41m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\MasterProductCategory;

use App\Actions\GrpAction;
use App\Enums\Catalogue\MasterProductCategory\MasterProductCategoryTypeEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Goods\MasterProductCategory;
use App\Models\Goods\MasterShop;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreMasterProductCategory extends GrpAction
{
    public function handle(MasterProductCategory|MasterShop $parent, array $modelData): MasterProductCategory
    {
        if ($parent instanceof MasterProductCategory) {
            data_set($modelData, 'group_id', $parent->group_id);
            data_set($modelData, 'master_department_id', $parent->id);
            data_set($modelData, 'master_shop_id', $parent->master_shop_id);
            data_set($modelData, 'master_parent_id', $parent->id);

            if ($parent->type == ProductCategoryTypeEnum::DEPARTMENT) {
                data_set($modelData, 'master_department_id', $parent->id);
            } elseif ($parent->type == ProductCategoryTypeEnum::SUB_DEPARTMENT) {
                data_set($modelData, 'master_sub_department_id', $parent->id);
            }
        } else {
            data_set($modelData, 'group_id', $parent->group_id);
            data_set($modelData, 'master_shop_id', $parent->id);
        }

        /** @var MasterProductCategory $masterProductCategory */
        $masterProductCategory = MasterProductCategory::create($modelData);
        $masterProductCategory->refresh();

        return $masterProductCategory;
    }

    public function rules(): array
    {
        return [
            'type'        => ['required', Rule::enum(MasterProductCategoryTypeEnum::class)],
            'code'        => [
                'required',
                $this->strict ? 'max:32' : 'max:255',
                new AlphaDashDot(),
                new IUnique(
                    table: 'master_product_categories',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'        => ['required', 'max:250', 'string'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'status'      => [
                'sometimes',
                'required',
                'boolean',
            ],
            'description' => ['sometimes', 'required', 'max:1500'],
            'created_at'  => ['sometimes', 'date'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }

    public function action(MasterShop|MasterProductCategory $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true): MasterProductCategory
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $group                = $parent->group;

        $this->initialisation($group, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    public function asController(MasterShop $masterShop, ActionRequest $request): MasterProductCategory
    {
        $this->initialisation($masterShop->group, $request);

        return $this->handle($masterShop, $this->validatedData);
    }

    public function inDepartment(MasterShop $masterShop, MasterProductCategory $masterProductCategory, ActionRequest $request): MasterProductCategory
    {
        $this->initialisation($masterShop->group, $request);

        return $this->handle(parent: $masterProductCategory, modelData: $this->validatedData);
    }


}
