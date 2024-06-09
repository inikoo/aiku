<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:32:39 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Notifications;

use App\Models\SysAdmin\User;
use App\Notifications\MeasurementShareNotification;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsObject;

class PublishNotification
{
    use AsObject;
    use AsAction;

    public string $commandSignature   = 'notification:publish';
    public string $commandDescription = 'Publish push notification';

    public function handle(User $user, $content, $target = ['fcm']): void
    {
        if (in_array('fcm', $target)) {
            $user->notify(new MeasurementShareNotification($content));
        }

        if (in_array('mail', $target)) {
            $user->notify();
        }
    }

    public function asCommand(): void
    {
        $user    = User::where('username', 'aiku')->first();
        $content = [
            'title' => 'Pallet delivery has been returned',
            'body'  => 'Hello, customer\'s pallet has been returned'
        ];

        $this->handle($user, $content);
    }
}
