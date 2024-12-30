<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 29 Dec 2024 02:37:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\MasterAsset;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateMasterAssets;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Goods\MasterAsset;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateMasterAsset extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;


    private MasterAsset $masterAsset;

    public function handle(MasterAsset $masterAsset, array $modelData): MasterAsset
    {
        $masterAsset = $this->update($masterAsset, $modelData);
        if ($masterAsset->wasChanged('status')) {
            GroupHydrateMasterAssets::dispatch($masterAsset->group)->delay($this->hydratorsDelay);
        }

        return $masterAsset;
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
                    table: 'master_assets',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'id', 'value' => $this->masterAsset->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'        => ['sometimes', 'required', 'max:250', 'string'],
            'price'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['sometimes', 'required', 'max:1500'],
            'rrp'         => ['sometimes', 'required', 'numeric'],
            'data'        => ['sometimes', 'array'],
            'status'      => ['sometimes', 'required', 'boolean'],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return false;
    }

    /**
     * @throws \Throwable
     */
    public function action(MasterAsset $masterAsset, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): MasterAsset
    {
        $this->strict = $strict;
        if (!$audit) {
            MasterAsset::disableAuditing();
        }

        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->masterAsset        = $masterAsset;

        $this->initialisationFromGroup($masterAsset->group, $modelData);

        return $this->handle($masterAsset, $this->validatedData);
    }

}
