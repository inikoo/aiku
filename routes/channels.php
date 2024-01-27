    <?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 16:41:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Models\SysAdmin\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('grp.personal.{userID}', function (User $user, int $userID) {
    return $userID === $user->id;
});

Broadcast::channel('grp.{groupID}.general', function (User $user) {
    return true;
});

Broadcast::channel('grp.live.users', function (User $user) {
    return [
        'id'    => $user->id,
        'alias' => $user->slug,
        'name'  => $user->contact_name,
    ];
});
