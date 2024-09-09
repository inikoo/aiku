<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 May 2023 11:42:32 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\TradeUnit;

use App\Actions\GrpAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateTradeUnits;
use App\Models\Goods\TradeUnit;
use App\Models\SysAdmin\Group;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;

class StoreTradeUnit extends GrpAction
{
    public function handle(Group $group, array $modelData): TradeUnit
    {
        /** @var TradeUnit $tradeUnit */
        $tradeUnit = $group->tradeUnits()->create($modelData);
        GroupHydrateTradeUnits::dispatch($group)->delay($this->hydratorsDelay);

        return $tradeUnit;
    }

    public function rules(): array
    {
        $rules =  [
            'code'             => [
                'required',
                'max:64',
                new AlphaDashDot(),
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'trade_units',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                    ]
                ),

            ],
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['sometimes', 'required', 'string', 'max:1024'],
            'barcode'          => ['sometimes', 'required'],
            'gross_weight'     => ['sometimes', 'required', 'numeric'],
            'net_weight'       => ['sometimes', 'required', 'numeric'],
            'marketing_weight' => ['sometimes', 'required', 'numeric'],
            'dimensions'       => ['sometimes', 'required'],
            'type'             => ['sometimes', 'required'],
            'image_id'         => ['sometimes', 'required', 'exists:media,id'],
            'data'             => ['sometimes', 'required'],

        ];

        if (!$this->strict) {
            $rules['source_id']    = ['sometimes', 'nullable', 'string'];
            $rules['source_slug']  = ['sometimes', 'nullable', 'string'];
            $rules['gross_weight'] = ['sometimes',  'nullable', 'numeric'];
            $rules['net_weight']   = ['sometimes',  'nullable', 'numeric'];
        }

        return $rules;
    }

    public function action(Group $group, array $modelData, int $hydratorDelay = 0, bool $strict = true): TradeUnit
    {
        $this->hydratorsDelay = $hydratorDelay;
        $this->strict    = $strict;
        $this->initialisation($group, $modelData);

        return $this->handle($group, $this->validatedData);
    }
}
