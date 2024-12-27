<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 27 Dec 2024 14:32:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\MasterProductCategory;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Goods\MasterProductCategory;
use App\Models\Goods\MasterShop;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateMasterProductCategory extends GrpAction
{
    use WithActionUpdate;

    private MasterProductCategory $masterProductCategory;

    private MasterShop $masterShop;

    public function handle(MasterProductCategory $masterProductCategory, array $modelData): MasterProductCategory
    {
        return $this->update($masterProductCategory, $modelData, ['data']);
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
            'status'      => ['sometimes', 'required', 'boolean'],
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
