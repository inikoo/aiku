<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 03 Sept 2024 17:16:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Adjustment;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateAdjustments;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateAdjustments;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateAdjustments;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Ordering\Adjustment\AdjustmentTypeEnum;
use App\Models\Ordering\Adjustment;
use Illuminate\Validation\Rule;

class UpdateAdjustment extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;

    public function handle(Adjustment $adjustment, array $modelData): Adjustment
    {
        $adjustment = $this->update($adjustment, $modelData, ['data']);

        if ($adjustment->wasChanged('type')) {
            OrganisationHydrateAdjustments::dispatch($adjustment->organisation)->delay($this->hydratorsDelay);
            GroupHydrateAdjustments::dispatch($adjustment->group)->delay($this->hydratorsDelay);
            ShopHydrateAdjustments::dispatch($adjustment->shop)->delay($this->hydratorsDelay);
        }

        return $adjustment;
    }


    public function rules(): array
    {
        $rules = [
        ];

        if (!$this->strict) {
            $rules                   = $this->noStrictUpdateRules($rules);
            $rules['net_amount']     = ['sometimes', 'numeric'];
            $rules['org_net_amount'] = ['sometimes', 'numeric'];
            $rules['grp_net_amount'] = ['sometimes', 'numeric'];
            $rules['tax_amount']     = ['sometimes', 'nullable', 'numeric'];
            $rules['org_tax_amount'] = ['sometimes', 'nullable', 'numeric'];
            $rules['grp_tax_amount'] = ['sometimes', 'nullable', 'numeric'];
            $rules['type']           = ['sometimes', 'required', Rule::enum(AdjustmentTypeEnum::class)];
        }

        return $rules;
    }

    public function action(Adjustment $adjustment, array $modelData, int $hydratorsDelay = 0, bool $strict = true): Adjustment
    {
        $this->strict   = $strict;
        $this->asAction = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($adjustment->shop, $modelData);

        return $this->handle($adjustment, $this->validatedData);
    }


}
