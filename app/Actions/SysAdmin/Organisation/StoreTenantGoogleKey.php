<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Models\SysAdmin\Organisation;
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
