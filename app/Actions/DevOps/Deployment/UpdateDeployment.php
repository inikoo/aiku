<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:58 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\DevOps\Deployment;

use App\Actions\WithActionUpdate;
use App\Models\Central\Deployment;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;

class UpdateDeployment
{
    use WithActionUpdate;

    public function handle(Deployment $deployment, array $modelData): Deployment
    {
        return $this->update($deployment, $modelData);
    }

    public function latest(ActionRequest $request): Deployment|JsonResponse
    {
        if ($deployment = Deployment::latest()->first()) {
            return $this->handle($deployment, $request->all());
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
