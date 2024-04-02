<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:46:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\CRM;

use App\Models\CRM\WebUser;
use Illuminate\Http\Resources\Json\JsonResource;

class WebUserResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var WebUser $webUser */
        $webUser = $this;

        return [
            'slug'       => $webUser->slug,
            'username'   => $webUser->email,
            'status'     => $webUser->status,
            'email'      => $webUser->email,
            'is_root'    => $webUser->is_root,
            'created_at' => $webUser->created_at,
            'updated_at' => $webUser->updated_at,
            'customer'   => CustomersResource::make($webUser->customer),

        ];
    }
}
