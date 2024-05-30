<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Feb 2024 10:29:58 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\UI;

use App\Http\Resources\HasSelfCall;
use App\Models\CRM\WebUser;
use Illuminate\Http\Resources\Json\JsonResource;

class LoggedWebUserResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var WebUser $webUser */
        $webUser = $this;

        return [
            'id'               => $webUser->id,
            'username'         => $webUser->username,
            'email'            => $webUser->email,
            'customer_id'      => $webUser->customer_id,
            'avatar_thumbnail' => !blank($webUser->image_id) ? $webUser->imageSources(0, 48) : null,

        ];
    }
}
