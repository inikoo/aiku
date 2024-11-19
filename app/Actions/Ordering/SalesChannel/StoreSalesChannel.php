<?php
/*
 * author Arya Permana - Kirin
 * created on 19-11-2024-16h-47m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\SalesChannel;

use App\Actions\Ordering\Purge\Hydrators\PurgeHydratePurgedOrders;
use App\Actions\Ordering\PurgedOrder\StorePurgedOrder;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Purge\PurgeStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Purge;
use App\Models\Ordering\SalesChannel;
use App\Rules\IUnique;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StoreSalesChannel extends OrgAction
{
    use WithNoStrictRules;

    public function handle(Shop $shop, array $modelData): SalesChannel
    {
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);


        $salesChannel = DB::transaction(function () use ($shop, $modelData) {
            /** @var SalesChannel $salesChannel */
            $salesChannel = $shop->salesChannels()->create($modelData);
            $salesChannel->refresh();
            $salesChannel->stats()->create();

            return $salesChannel;
        });

        return $salesChannel;
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
            'code' => [
                'required',
                'max:16',
                'alpha_dash',
                new IUnique(
                    table: 'sales_channels',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                    ]
                ),
            ],
            'name'  => ['required', 'string']
        ];

        if (!$this->strict) {
            $rules             = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    public function asController(Shop $shop, ActionRequest $request): SalesChannel
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function action(Shop $shop, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): SalesChannel
    {
        if (!$audit) {
            Purge::disableAuditing();
        }

        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }
}
