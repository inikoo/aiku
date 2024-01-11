<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 16:41:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('grp.{group:id}.personal.{userID}', function (Group $group, User $user, int $userID) {
    return $userID === $user->id;
});

Broadcast::channel('grp.{group:id}.general', function (Group $group, User $user) {
    return true;
});

Broadcast::channel('grp.{group:id}.live.users', function (Group $group, User $user) {
    return [
        'id'    => $user->id,
        'alias' => $user->slug,
        'name'  => $user->contact_name,
    ];
});
