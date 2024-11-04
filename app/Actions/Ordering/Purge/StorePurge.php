<?php
/*
 * author Arya Permana - Kirin
 * created on 04-11-2024-08h-45m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\Purge;

use App\Actions\Ordering\PurgedOrder\StorePurgedOrder;
use App\Actions\OrgAction;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Purge;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePurge extends OrgAction
{
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
            ->get();

        foreach($orders as $order)
        {
            StorePurgedOrder::make()->action($purge, $order);
        }

        return $purge;
    }

    public function authorize(ActionRequest $request)
    {
        if($this->asAction)
        {
            return true;
        }

        return true;
    }

    public function rules()
    {
        return [
            'type'              => ['required', Rule::enum(PurgeTypeEnum::class)],
            'scheduled_at'      => ['required', 'date'],
        ];
    }

    public function asController(Shop $shop, ActionRequest $request)
    {
        $this->initialisationFromShop($shop, $request);
        return $this->handle($shop, $this->validatedData);
    }
}