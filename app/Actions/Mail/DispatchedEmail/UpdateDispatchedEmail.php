<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\DispatchedEmail;

use App\Actions\WithActionUpdate;
use App\Models\Mail\DispatchedEmail;

class UpdateDispatchedEmail
{
    use WithActionUpdate;

    public function handle(DispatchedEmail $dispatchedEmail, array $modelData): DispatchedEmail
    {
        return $this->update($dispatchedEmail, $modelData, ['data']);
    }
}
