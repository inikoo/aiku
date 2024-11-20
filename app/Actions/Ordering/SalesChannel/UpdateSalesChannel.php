<?php
/*
 * author Arya Permana - Kirin
 * created on 19-11-2024-16h-54m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\SalesChannel;

use App\Actions\Ordering\Purge\Hydrators\PurgeHydratePurgedOrders;
use App\Actions\Ordering\PurgedOrder\StorePurgedOrder;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
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

class UpdateSalesChannel extends OrgAction
{
    use WithNoStrictRules;
    use WithActionUpdate;

    public function handle(SalesChannel $salesChannel, array $modelData): SalesChannel
    {
        $salesChannel = $this->update($salesChannel, $modelData);

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
            'name'  => ['sometimes', 'string']
        ];

        if (!$this->strict) {
            $rules             = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function asController(SalesChannel $salesChannel, ActionRequest $request): SalesChannel
    {
        $this->initialisationFromShop($salesChannel->shop, $request);

        return $this->handle($salesChannel, $this->validatedData);
    }

    public function action(SalesChannel $salesChannel, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): SalesChannel
    {
        if (!$audit) {
            Purge::disableAuditing();
        }

        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisationFromShop($salesChannel->shop, $modelData);

        return $this->handle($salesChannel, $this->validatedData);
    }
}
