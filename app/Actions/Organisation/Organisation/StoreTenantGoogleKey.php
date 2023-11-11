<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation;

use App\Models\Organisation\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTenantGoogleKey
{
    use AsAction;
    use WithAttributes;

    public function handle(Organisation $organisation, array $modelData): Organisation
    {
        $organisation->update([
            'data' => json_encode([
                'google_cloud_client_id'     => $modelData['google_cloud_client_id'],
                'google_cloud_client_secret' => $modelData['google_cloud_client_secret']
            ])
        ]);

        return $organisation;
    }

    public function action(Organisation $organisation, $modelData): Organisation
    {
        return $this->handle($organisation, $modelData);
    }
}
