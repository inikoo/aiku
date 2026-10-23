<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 26-05-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Masters\MasterProductCategory;

use App\Actions\Masters\MasterProductCategory\Hydrators\MasterDepartmentHydrateMasterSubDepartments;
use App\Actions\Masters\MasterShop\Hydrators\MasterShopHydrateMasterSubDepartments;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithMastersEditAuthorisation;
use App\Enums\Catalogue\MasterProductCategory\MasterProductCategoryTypeEnum;
use App\Models\Masters\MasterProductCategory;
use App\Models\Masters\MasterShop;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreMasterSubDepartment extends OrgAction
{
    use WithMastersEditAuthorisation;
    /**
     * @var \App\Models\Masters\MasterProductCategory|\App\Models\Masters\MasterShop
     */
    private MasterShop|MasterProductCategory $parent;

    public function handle(MasterProductCategory|MasterShop $parent, array $modelData, bool $createChildren = true): MasterProductCategory
    {
        data_set($modelData, 'type', MasterProductCategoryTypeEnum::SUB_DEPARTMENT);

        $masterSubDepartment = StoreMasterProductCategory::run($parent, $modelData, $createChildren);

        MasterDepartmentHydrateMasterSubDepartments::dispatch($masterSubDepartment->masterDepartment)->delay($this->hydratorsDelay);
        MasterShopHydrateMasterSubDepartments::dispatch($masterSubDepartment->masterShop)->delay($this->hydratorsDelay);

        return $masterSubDepartment;
    }

    public function rules(): array
    {
        $masterShopId = $this->parent instanceof MasterShop
            ? $this->parent->id
            : $this->parent->master_shop_id;

        $rules = [
            'code'        => [
                'required',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'master_product_categories',
                    extraConditions: [
                        ['column' => 'type', 'value' => MasterProductCategoryTypeEnum::SUB_DEPARTMENT->value],
                        ['column' => 'master_shop_id', 'value' => $masterShopId],
                        ['column' => 'deleted_at', 'operator' => 'null'],
                    ]
                ),
            ],
            'name'        => ['required', 'max:250', 'string'],
            'description' => ['sometimes', 'nullable', 'max:1500'],

        ];

        if ($this->parent instanceof MasterShop) {
            $rules['master_department_id'] = [
                'required',
                'integer',
                Rule::exists('master_product_categories', 'id')
                    ->where('master_shop_id', $masterShopId)
                    ->where('type', MasterProductCategoryTypeEnum::DEPARTMENT->value)
                    ->whereNull('deleted_at'),
            ];
        }

        return $rules;
    }


    public function action(MasterProductCategory $masterDepartment, array $modelData, bool $createChildren = true): MasterProductCategory
    {
        $this->parent   = $masterDepartment;
        $this->asAction = true;
        $this->initialisationFromGroup(group(), $modelData);

        return $this->handle($masterDepartment, $this->validatedData, $createChildren);
    }

    public function asController(MasterShop $masterShop, ActionRequest $request): MasterProductCategory
    {
        $this->parent = $masterShop;
        $this->initialisationFromGroup(group(), $request);

        $modelData        = $this->validatedData;
        $masterDepartment = MasterProductCategory::find(Arr::pull($modelData, 'master_department_id'));

        return $this->handle($masterDepartment, $modelData);
    }

    public function inMasterDepartment(MasterProductCategory $masterDepartment, ActionRequest $request): MasterProductCategory
    {
        $this->parent = $masterDepartment;
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($masterDepartment, $this->validatedData);
    }

    public function htmlResponse(MasterProductCategory $masterSubDepartment, ActionRequest $request): RedirectResponse
    {
        if ($this->parent instanceof MasterShop) {
            return Redirect::route('grp.masters.master_shops.show.master_sub_departments.show', [
                'masterShop'          => $this->parent->slug,
                'masterSubDepartment' => $masterSubDepartment->slug,
            ]);
        }

        return Redirect::route('grp.masters.master_shops.show.master_departments.show.master_sub_departments.show', [
            'masterShop'          => $masterSubDepartment->masterShop->slug,
            'masterDepartment'    => $masterSubDepartment->parent->slug,
            'masterSubDepartment' => $masterSubDepartment->slug,
        ]);
    }


}
