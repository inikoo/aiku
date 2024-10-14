<?php
/*
 * author Arya Permana - Kirin
 * created on 14-10-2024-14h-16m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\MasterProduct;

use App\Actions\Catalogue\Asset\StoreAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateProductVariants;
use App\Actions\GrpAction;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\Product\ProductUnitRelationshipType;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\MasterProduct;
use App\Models\Catalogue\MasterProductCategory;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\Goods\TradeUnit;
use App\Models\Inventory\OrgStock;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreMasterProduct extends GrpAction
{
    public function handle(Group|MasterProductCategory $parent, array $modelData): MasterProduct
    {
        $status = false;
        if (in_array(Arr::get($modelData, 'state'), [ProductStateEnum::ACTIVE, ProductStateEnum::DISCONTINUING])) {
            $status = true;
        }
        data_set($modelData, 'status', $status);

        if($parent instanceof Group)
        {
            $masterProduct = $parent->masterProducts()->create($modelData);
        } 
        else
        {
            data_set($modelData, 'group_id', $parent->group_id);
            data_set($modelData, 'master_department_id', $parent->department_id);
    
            if ($parent->type == ProductCategoryTypeEnum::FAMILY) {
                data_set($modelData, 'master_family_id', $parent->id);
            }
            if ($parent->type == ProductCategoryTypeEnum::SUB_DEPARTMENT) {
                data_set($modelData, 'master_sub_department_id', $parent->id);
            }
            $masterProduct = $parent->masterProducts()->create($modelData);
        }

        return $masterProduct;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
    }

    public function rules(): array
    {
        $rules = [
            'code'               => [
                'required',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'assets',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => ProductStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'               => ['required', 'max:250', 'string'],
            'state'              => ['sometimes', 'required', Rule::enum(ProductStateEnum::class)],
            'master_family_id'          => [
                'sometimes',
                'required',
                Rule::exists('master_product_categories', 'id')
                    ->where('group_id', $this->group->id)
                    ->where('type', ProductCategoryTypeEnum::FAMILY)
            ],
            'master_department_id'      => [
                'sometimes',
                'required',
                Rule::exists('master_product_categories', 'id')
                    ->where('group_id', $this->group->id)
                    ->where('type', ProductCategoryTypeEnum::DEPARTMENT)
            ],
            'master_sub_department_id'      => [
                'sometimes',
                'required',
                Rule::exists('master_product_categories', 'id')
                    ->where('group_id', $this->group->id)
                    ->where('type', ProductCategoryTypeEnum::SUB_DEPARTMENT)
            ],
            'image_id'           => ['sometimes', 'required', 'exists:media,id'],
            'price'              => ['required', 'numeric', 'min:0'],
            'unit'               => ['sometimes', 'required', 'string'],
            'rrp'                => ['sometimes', 'required', 'numeric', 'min:0'],
            'description'        => ['sometimes', 'required', 'max:1500'],
            'data'               => ['sometimes', 'array'],
            'settings'           => ['sometimes', 'array'],
            'is_main'            => ['required', 'boolean'],
            'main_product_id'    => [
                'sometimes',
                'nullable',
                Rule::exists('master_products', 'id')
                    ->where('group_id', $this->group->id)
            ],
            'variant_ratio'      => ['sometimes', 'required', 'numeric', 'gt:0'],
            'variant_is_visible' => ['sometimes', 'required', 'boolean'],

        ];

        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(Group|MasterProductCategory $parent, array $modelData, int $hydratorsDelay = 0, $strict = true, $audit = true): MasterProduct
    {
        if (!$audit) {
            MasterProduct::disableAuditing();
        }

        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;

        if ($parent instanceof Group) {
            $group = $parent;
        } else {
            $group = $parent->group;
        }

        $this->initialisation($group, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

}
