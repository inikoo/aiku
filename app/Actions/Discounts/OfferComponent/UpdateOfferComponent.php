<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:04:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\OfferComponent;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Discounts\OfferComponent;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateOfferComponent extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;


    private OfferComponent $offerComponent;

    public function handle(OfferComponent $offerComponent, array $modelData): OfferComponent
    {
        return $this->update($offerComponent, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("discounts.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        $rules =  [
            'code'       => [
                'sometimes',
                new IUnique(
                    table: 'offer_components',
                    extraConditions: [
                        [
                            'column' => 'shop_id',
                            'value'  => $this->shop->id,
                        ],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->offerComponent->id
                        ]
                    ]
                ),
                'start_at'      => ['sometimes', 'date'],
                'end_at'        => ['sometimes', 'nullable', 'date'],
                'max:64',
                'alpha_dash'
            ],
            'data' => ['sometimes', 'required']
        ];

        if (!$this->strict) {
            $rules = $this->noStrictUpdateRules($rules);
            $rules['trigger_scope']        = ['sometimes', 'string'];
            $rules['target_type']        = ['sometimes', 'string'];
            $rules['start_at']        = ['sometimes', 'nullable', 'date'];
        }

        return $rules;
    }

    public function action(OfferComponent $offerComponent, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): OfferComponent
    {
        if (!$audit) {
            OfferComponent::disableAuditing();
        }
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $this->offerComponent          = $offerComponent;
        $this->initialisationFromShop($offerComponent->shop, $modelData);

        return $this->handle($offerComponent, $this->validatedData);
    }


}
