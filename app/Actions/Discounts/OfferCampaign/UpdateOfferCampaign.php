<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 15:55:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\OfferCampaign;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Discounts\OfferCampaign\OfferCampaignStateEnum;
use App\Models\Discounts\OfferCampaign;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateOfferCampaign extends OrgAction
{
    use WithActionUpdate;

    public function handle(OfferCampaign $offerCampaign, array $modelData): OfferCampaign
    {
        return $this->update($offerCampaign, $modelData);
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
        $rules = [
            'name'   => ['sometimes','required', 'max:250', 'string'],
            'status' => ['sometimes','required', 'boolean'],
            'state'  => ['sometimes','required', Rule::enum(OfferCampaignStateEnum::class)],
        ];

        if (!$this->strict) {
            $rules['fetched_at']      = ['sometimes', 'date'];
            $rules['last_fetched_at'] = ['sometimes', 'date'];
            $rules['source_id']       = ['sometimes', 'string', 'max:255'];
            $rules['created_at']      = ['sometimes', 'date'];
        }

        return $rules;
    }

    public function action(OfferCampaign $offerCampaign, array $modelData, bool $strict = true, bool $audit = true): OfferCampaign
    {
        if (!$audit) {
            OfferCampaign::disableAuditing();
        }
        $this->asAction = true;
        $this->strict   = $strict;
        $this->initialisationFromShop($offerCampaign->shop, $modelData);

        return $this->handle($offerCampaign, $this->validatedData);
    }

    public function asController(OfferCampaign $offerCampaign, ActionRequest $request): OfferCampaign
    {
        $this->initialisationFromShop($offerCampaign->shop, $request);

        return $this->handle($offerCampaign, $this->validatedData);
    }


}
