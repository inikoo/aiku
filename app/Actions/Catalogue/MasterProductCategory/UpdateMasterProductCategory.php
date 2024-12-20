<?php

/*
 * author Arya Permana - Kirin
 * created on 14-10-2024-13h-53m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\MasterProductCategory;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\MasterProductCategory;
use App\Models\Catalogue\MasterShop;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateMasterProductCategory extends GrpAction
{
    use WithActionUpdate;

    private MasterProductCategory $masterProductCategory;

    private MasterShop $masterShop;

    public function handle(MasterProductCategory $masterProductCategory, array $modelData): MasterProductCategory
    {
        // dd($modelData);
        $masterProductCategory = $this->update($masterProductCategory, $modelData, ['data']);

        return $masterProductCategory;
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
            'code'        => [
                'sometimes',
                $this->strict ? 'max:32' : 'max:255',
                new AlphaDashDot(),
                new IUnique(
                    table: 'master_product_categories',
                    extraConditions: [
                        ['column' => 'master_shop_id', 'value' => $this->masterShop->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'type', 'value' => $this->masterProductCategory->type, 'operator' => '='],
                        ['column' => 'id', 'value' => $this->masterProductCategory->id, 'operator' => '!=']

                    ]
                ),
            ],
            'name'        => ['sometimes', 'max:250', 'string'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'state'       => ['sometimes', 'required', Rule::enum(ProductCategoryStateEnum::class)],
            'description' => ['sometimes', 'required', 'max:1500'],
            'master_department_id' => ['sometimes', 'nullable', 'exists:product_categories,id'],
            'master_sub_department_id' => ['sometimes', 'nullable', 'exists:product_categories,id']
        ];

        return $rules;
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($this->masterProductCategory->type == ProductCategoryTypeEnum::DEPARTMENT) {
            $this->set('master_department_id', null);
        }
    }

    public function action(MasterProductCategory $masterProductCategory, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): MasterProductCategory
    {
        $this->asAction        = true;
        $this->masterProductCategory = $masterProductCategory;
        $this->masterShop = $masterProductCategory->masterShop;
        $this->hydratorsDelay  = $hydratorsDelay;
        $this->strict          = $strict;
        $this->initialisation($masterProductCategory->group, $modelData);

        return $this->handle($masterProductCategory, $this->validatedData);
    }
}
