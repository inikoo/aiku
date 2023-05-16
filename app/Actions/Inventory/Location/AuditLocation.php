<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 15 May 2023 15:10:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location;

use App\Actions\WithActionUpdate;
use App\Models\Inventory\Location;
use Lorisleiva\Actions\ActionRequest;

class AuditLocation
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(Location $location): Location
    {
        $this->update($location, [
            'audited_at' => now(),
        ]);

        return $location;
    }

    public function action(Location $location): Location
    {
        $this->asAction = true;

        return $this->handle($location);
    }

    public function asController(Location $location, ActionRequest $request): Location
    {
        $request->validate();

        return $this->handle($location);
    }
}
