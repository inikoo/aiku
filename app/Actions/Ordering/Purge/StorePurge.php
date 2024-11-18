<?php
/*
 * author Arya Permana - Kirin
 * created on 04-11-2024-08h-45m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\Purge;

use App\Actions\Ordering\Purge\Hydrators\PurgeHydratePurgedOrders;
use App\Actions\Ordering\PurgedOrder\StorePurgedOrder;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Purge;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StorePurge extends OrgAction
{
    use WithNoStrictRules;

    public function handle(Shop $shop, $modelData): Purge
    {
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);
        /** @var Purge $purge */
        $purge = $shop->purges()->create($modelData);
        $purge->refresh();
        $purge->stats()->create([
            'currency_id' => $shop->currency_id
        ]);
        $dateThreshold = Carbon::now()->subDays(30);
        $orders = $shop->orders()
            ->where('updated_at', '<', $dateThreshold)
            ->where('state', OrderStateEnum::CREATING)
            ->get();

        foreach ($orders as $order) {
            StorePurgedOrder::make()->action($purge, $order);
        }

        PurgeHydratePurgedOrders::dispatch($purge);

        return $purge;
    }

    public function authorize(ActionRequest $request)
    {
        if ($this->asAction) {
            return true;
        }

        return true;
    }

    public function afterValidator(Validator $validator): void
    {
        $dateThreshold = Carbon::now()->subDays(30);
        $numberEligiblePurge = $this->shop->orders()
        ->where('updated_at', '<', $dateThreshold)
        ->where('state', OrderStateEnum::CREATING)
        ->count();

        if ($this->strict && $numberEligiblePurge == 0) {
            $message = __("There Are No Eligble Orders to Purge");
            $validator->errors()->add('purge', $message);
        }
    }

    public function rules()
    {
        $rules = [
            'type'              => ['required', Rule::enum(PurgeTypeEnum::class)],
            'scheduled_at'      => ['required', 'date'],
        ];

        if (!$this->strict) {

            $rules = $this->noStrictStoreRules($rules);

        }
        return $rules;
    }

    public function asController(Shop $shop, ActionRequest $request)
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function action(Shop $shop, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true)
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
