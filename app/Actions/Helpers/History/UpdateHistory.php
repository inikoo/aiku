<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 04 Oct 2024 11:58:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\History;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Helpers\History;

class UpdateHistory extends GrpAction
{
    use WithActionUpdate;


    public function handle(History $history, array $modelData): History
    {
        return $this->update($history, $modelData, ['data','new_values','old_values']);
    }


    public function rules(): array
    {
        return [
            'new_values' => ['sometimes', 'array'],
            'old_values' => ['sometimes', 'array'],
            'last_fetched_at' => ['required', 'date'],
        ];
    }



    public function action(History $history, array $modelData, int $hydratorsDelay = 0): History
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($history->group, $modelData);
        return $this->handle($history, $this->validatedData);
    }


}
