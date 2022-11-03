<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 04 Oct 2022 12:25:51 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Deployment;

use App\Models\Central\Deployment;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;


class ShowDeployment
{
    use AsAction;

    public function handle(Deployment $deployment): Deployment
    {
        return $deployment;
    }

    public function latest(): JsonResponse|Deployment
    {
        if ($deployment = Deployment::latest()->first()) {
            return $deployment;
        } else {
            return response()->json(
                [
                    'message' => 'There is no deployments.'
                ],
                404
            );
        }
    }

}
