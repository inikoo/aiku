<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 04 Oct 2022 12:51:21 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Deployment;

use App\Models\Central\Deployment;
use App\Actions\WithActionUpdate;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;


class UpdateDeployment
{
    use WithActionUpdate;

    public function handle(Deployment $deployment, array $modelData): Deployment
    {
        return $this->update($deployment, $modelData);
    }

    function latest(ActionRequest $request): Deployment|JsonResponse
    {
        if ($deployment = Deployment::latest()->first()) {
           return $this->handle($deployment,$request->all());
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
