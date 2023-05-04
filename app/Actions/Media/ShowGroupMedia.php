<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 19:11:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Media;

use App\Models\Media\GroupMedia;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowGroupMedia
{
    use AsAction;


    public function asController(GroupMedia $groupMedia): GroupMedia
    {
        return $groupMedia;
    }


    public function htmlResponse(GroupMedia $groupMedia)
    {

        $headers = [
            'Content-Type'   => $groupMedia->mime_type,
            'Content-Length' => $groupMedia->size,
        ];
        return response()->file($groupMedia->getPath(), $headers);
    }
}
