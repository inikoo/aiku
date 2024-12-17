<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 13 Dec 2024 12:47:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\SubscriptionEvent;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Comms\SubscriptionEvent\SubscriptionEventTypeEnum;
use App\Models\Comms\SubscriptionEvent;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreSubscriptionEvent extends OrgAction
{
    use WithNoStrictRules;


    public function handle(Customer|Prospect $parent, array $modelData): SubscriptionEvent
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'shop_id', $parent->shop_id);

        return $parent->subscriptionEvents()->create($modelData);
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

            'type'      => ['required', Rule::enum(SubscriptionEventTypeEnum::class)],
            'outbox_id' => ['required', Rule::Exists('outboxes', 'id')->where('shop_id', $this->shop->id)],
            'origin_type' => ['nullable', 'string'],
            'origin_id'   => ['nullable', 'integer'],

        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


    public function action(Customer|Prospect $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true): SubscriptionEvent
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;


        $this->initialisationFromShop($parent->shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }


}
