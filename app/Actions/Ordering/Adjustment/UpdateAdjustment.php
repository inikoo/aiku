<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 03 Sept 2024 17:16:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Adjustment;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Ordering\Adjustment;

class UpdateAdjustment extends OrgAction
{
    use WithActionUpdate;


    public function handle(Adjustment $adjustment, array $modelData): Adjustment
    {
        return $this->update($adjustment, $modelData, ['data']);
    }


    public function rules(): array
    {
        return [

            'amount'          => ['sometimes', 'required', 'numeric'],
            'last_fetched_at' => ['sometimes', 'date'],
        ];
    }

    public function action(Adjustment $adjustment, array $modelData, bool $strict = true, bool $audit = true): Adjustment
    {
        $this->strict = $strict;

        $this->asAction   = true;
        $this->initialisationFromShop($adjustment->shop, $modelData);

        return $this->handle($adjustment, $this->validatedData);
    }


}
