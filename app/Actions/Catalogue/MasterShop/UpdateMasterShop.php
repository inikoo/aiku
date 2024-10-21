<?php
/*
 * author Arya Permana - Kirin
 * created on 21-10-2024-15h-08m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\MasterShop;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\MasterProductCategory;
use App\Models\Catalogue\MasterShop;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateMasterShop extends GrpAction
{
    use WithActionUpdate;

    public function handle(MasterShop $masterShop, array $modelData): MasterShop
    {
        $masterProductCategory = $this->update($masterShop, $modelData, ['data']);

        return $masterShop;
    }

    public function rules(): array
    {
        $rules = [
            'type'                 => ['sometimes', Rule::enum(ShopTypeEnum::class)],
            'state'                 => ['sometimes', Rule::enum(ShopStateEnum::class)],
            'name'                 => ['sometimes', 'max:250', 'string'],
            'image_id'             => ['sometimes', 'sometimes', 'exists:media,id'],
        ];

        return $rules;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }

    public function action(MasterShop $masterShop, array $modelData, int $hydratorsDelay = 0, bool $strict = true): MasterShop
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;

        $this->initialisation($masterShop->group, $modelData);

        return $this->handle($masterShop, $this->validatedData);
    }

    public function asController(MasterShop $masterShop, ActionRequest $request): MasterShop
    {
        $this->initialisation($masterShop->group, $request);

        return $this->handle($masterShop, $this->validatedData);
    }

}

// public function htmlResponse(ProductCategory $productCategory, ActionRequest $request): RedirectResponse
// {
//     if (class_basename($productCategory->parent) == 'ProductCategory') {
//         return Redirect::route('grp.org.shops.show.catalogue.departments.show.families.show', [
//             'organisation' => $productCategory->organisation->slug,
//             'shop'         => $productCategory->shop->slug,
//             'department'   => $productCategory->parent->slug,
//             'family'       => $productCategory->slug,
//         ]);
//     } else {
//         return Redirect::route('grp.org.shops.show.catalogue.departments.show', [
//             'organisation' => $productCategory->organisation->slug,
//             'shop'         => $productCategory->shop->slug,
//             'department'   => $productCategory->slug,
//         ]);
//     }
// }
