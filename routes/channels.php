<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 16:41:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Models\CRM\WebUser;
use App\Models\SysAdmin\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('grp.personal.{userID}', function (User $user, int $userID) {
    return $userID === $user->id;
});

Broadcast::channel('grp.{groupID}.general', function (User $user, int $groupID) {
    return $user->group_id === $groupID;
});

Broadcast::channel('grp.{groupID}.fulfilmentCustomer.{userID}', function (User $user, int $groupID, int $userID) {
    return $user->id === $userID;
});

Broadcast::channel('grp.live.users', function (User $user) {
    return [
        'id'    => $user->id,
        'alias' => $user->slug,
        'name'  => $user->contact_name,
    ];
});

Broadcast::channel('retina.{websiteID}.website', function (Webuser $webUser, int $websiteID) {
    return $websiteID===$webUser->website_id;
});

Broadcast::channel('retina.{customerID}.customer', function (Webuser $webUser, int $customerID) {
    return $customerID===$webUser->customer_id;
});

Broadcast::channel('retina.personal.{webUserID}', function (Webuser $webUser, int $webUserID) {
    return $webUserID === $webUser->id;
});

Broadcast::channel('retina.active.users', function (Webuser $webUser) {
    return [
        'id'    => $webUser->id,
        'alias' => $webUser->slug,
    ];
});
