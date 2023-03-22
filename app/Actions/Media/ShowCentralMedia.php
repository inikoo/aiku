<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Mar 2023 15:41:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Media;

use App\Models\Central\CentralMedia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ShowCentralMedia
{
    use AsAction;


    public function authorize(ActionRequest $request): bool
    {
        return $request->route('centralMedia')->tenants->pluck('id')->contains(app('currentTenant')->id);
    }

    /*
        public function getAuthorizationFailure(): void
        {
          // todo: show a image
        }
    */

    public function asController(CentralMedia $centralMedia, ActionRequest $request): CentralMedia
    {
        return $centralMedia;
    }


    public function htmlResponse(CentralMedia $centralMedia): BinaryFileResponse
    {
        $headers = [
            'Content-Type'   => $centralMedia->mime_type,
            'Content-Length' => $centralMedia->size,
        ];

        return response()->file($centralMedia->getPath(), $headers);
    }
}
