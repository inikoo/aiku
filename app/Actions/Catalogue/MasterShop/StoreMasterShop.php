<?php

/*
 * author Arya Permana - Kirin
 * created on 21-10-2024-14h-53m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\MasterShop;

use App\Actions\GrpAction;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Goods\MasterShop;
use App\Models\SysAdmin\Group;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreMasterShop extends GrpAction
{
    public function handle(Group $group, array $modelData): MasterShop
    {
        data_set($modelData, 'group_id', $group->id);
        // dd($modelData);
        /** @var MasterShop $masterShop */
        $masterShop = MasterShop::create($modelData);
        $masterShop->refresh();

        return $masterShop;
    }

    public function rules(): array
    {
        $rules = [
            'type'                 => ['required', Rule::enum(ShopTypeEnum::class)],
            'state'                 => ['sometimes', Rule::enum(ShopStateEnum::class)],
            'code'                 => [
                'required',
                $this->strict ? 'max:32' : 'max:255',
                new AlphaDashDot(),
                new IUnique(
                    table: 'master_shops',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'                 => ['required', 'max:250', 'string'],
            'image_id'             => ['sometimes', 'required', 'exists:media,id'],
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

    public function action(Group $group, array $modelData, int $hydratorsDelay = 0, bool $strict = true): MasterShop
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;

        $this->initialisation($group, $modelData);

        return $this->handle($group, $this->validatedData);
    }

    public function asController(Group $group, ActionRequest $request): MasterShop
    {
        $this->initialisation($group, $request);

        return $this->handle($group, $this->validatedData);
    }

}
