<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 08 Jun 2023 14:34:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use OwenIt\Auditing\Models\Audit;

class StoreUserHistories
{
    use AsAction;
    use WithAttributes;

    public function handle(array $objectData = []): Audit
    {
        return Audit::create($objectData);
    }
}
