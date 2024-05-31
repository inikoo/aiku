<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 21:10:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\UI;

use App\Http\Resources\HasSelfCall;
use App\Http\Resources\SysAdmin\NotificationsResource;
use App\Models\SysAdmin\User;
use Illuminate\Http\Resources\Json\JsonResource;

class LoggedUserResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var User $user */
        $user = $this;
        return [
            'id'               => $user->id,
            'username'         => $user->username,
            'email'            => $user->email,
            'avatar_thumbnail' => !blank($user->image_id) ? $user->imageSources(0, 48) : null,
            'notifications'    => NotificationsResource::collection($user->notifications()->orderBy('created_at', 'desc')->limit(10)->get())->collection
        ];
    }
}
