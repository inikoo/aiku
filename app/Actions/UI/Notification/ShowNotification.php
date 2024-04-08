<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Notification;

use App\Actions\UI\WithInertia;
use App\Http\Resources\SysAdmin\NotificationResource;
use App\Models\Notification;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowNotification
{
    use AsAction;
    use WithInertia;

    public function asController(Notification $notification): Notification
    {
        $notification = ReadNotification::run($notification);

        $notification->refresh();

        return $notification;
    }

    public function jsonResponse(Notification $notification): NotificationResource
    {
        return new NotificationResource($notification);
    }
}
