<?php
/*
 * author Arya Permana - Kirin
 * created on 14-10-2024-14h-36m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\MasterProduct;

use App\Actions\Catalogue\Asset\StoreAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateProductVariants;
use App\Actions\GrpAction;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
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

class UpdateMasterProduct extends GrpAction
{
    use WithActionUpdate;

    private MasterProduct $masterProduct;

    public function handle(MasterProduct $masterProduct, array $modelData): MasterProduct
    {
        $masterProduct = $this->update($masterProduct, $modelData);

        return $masterProduct;
    }

    public function rules(): array
    {
        $rules = [
            'code'        => [
                'sometimes',
                'required',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'master_products',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'id', 'value' => $this->masterProduct->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'        => ['sometimes', 'required', 'max:250', 'string'],
            'price'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['sometimes', 'required', 'max:1500'],
            'rrp'         => ['sometimes', 'required', 'numeric'],
            'data'        => ['sometimes', 'array'],
            'settings'    => ['sometimes', 'array'],
            'status'      => ['sometimes', 'required', 'boolean'],
            'state'       => ['sometimes', 'required', Rule::enum(ProductStateEnum::class)],
        ];

        return $rules;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
    }

    /**
     * @throws \Throwable
     */
    public function action(MasterProduct $masterProduct, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): MasterProduct
    {
        if (!$audit) {
            MasterProduct::disableAuditing();
        }

        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->masterProduct        = $masterProduct;

        $this->initialisation($masterProduct->group, $modelData);

        return $this->handle($masterProduct, $this->validatedData);
    }

}
