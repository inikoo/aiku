<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Feb 2024 12:51:58 Malaysia Time, Madrid Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\CRM;

use App\Models\CRM\WebUser;
use Illuminate\Http\Resources\Json\JsonResource;

class WebUsersResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var WebUser $webUser */
        $webUser = $this;

        return [
            'slug'        => $webUser->slug,
            'username'    => $webUser->username,
            'status'      => $webUser->status,
            'email'       => $webUser->email,
            'is_root'     => $webUser->is_root,
            'created_at'  => $webUser->created_at
        ];
    }
}
