<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:58 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\DevOps\Deployment;

use App\Http\Resources\DevOps\DeploymentResource;
use App\Models\DevOps\Deployment;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDeployment
{
    use AsAction;

    public function handle(Deployment $deployment): Deployment
    {
        return $deployment;
    }

    public function latest(): JsonResponse|DeploymentResource
    {
        if ($deployment = Deployment::latest()->first()) {
            return new DeploymentResource($deployment);
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
