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
        ;

        return $tradeUnit;
    }

    public function rules(): array
    {
        return [
            'code'         => [
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
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['sometimes', 'required', 'string', 'max:1024'],
            'barcode'      => ['sometimes', 'required'],
            'gross_weight' => ['sometimes', 'required', 'numeric'],
            'net_weight'   => ['sometimes', 'required', 'numeric'],
            'dimensions'   => ['sometimes', 'required'],
            'type'         => ['sometimes', 'required'],
            'image_id'     => ['sometimes', 'required', 'exists:media,id'],
            'data'         => ['sometimes', 'required'],
            'source_id'    => ['sometimes', 'nullable', 'string'],
            'source_slug'  => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function action(Group $group, array $modelData, int $hydratorDelay = 0): TradeUnit
    {
        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($group, $modelData);

        return $this->handle($group, $this->validatedData);
    }
}
