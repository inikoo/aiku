<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\TopUp;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Accounting\TopUp;

class UpdateTopUp extends OrgAction
{
    use WithActionUpdate;

    public function handle(TopUp $topUp, array $modelData): TopUp
    {
        $this->update($topUp, $modelData);

        $topUp->refresh();
        return $topUp;
    }

    public function rules(): array
    {
        return [
            'amount'           => ['required', 'numeric'],
            'reference'        => ['sometimes', 'string'],
            'source_id'        => ['sometimes', 'string'],
        ];
    }

    public function action(TopUp $topUp, $modelData): TopUp
    {
        $this->asAction = true;
        $this->initialisation($topUp->organisation, $modelData);
        return $this->handle($topUp, $modelData);
    }
}
