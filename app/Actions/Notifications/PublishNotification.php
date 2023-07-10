<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Notifications;

use App\Actions\Mail\EmailAddress\SendEmailAddress;
use App\Models\Auth\User;
use App\Models\Tenancy\Tenant;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsObject;

class PublishNotification
{
    use AsObject;
    use AsAction;

    public string $commandSignature   = 'notification:publish';
    public string $commandDescription = 'Publish push notification';

    public function handle(Collection $users, $content, $target = ['mail', 'fcm']): void
    {
        foreach ($users as $user) {
            if(in_array('fcm', $target)) {
                PublishPushNotification::dispatch($user, $content);
            }

            if(in_array('mail', $target)) {
                //                SendEmailAddress::run($content, $user->email);
            }
        }
    }

    public function asCommand(): void
    {
        Tenant::where('slug', 'aw')->first()->makeCurrent();
        $users   = User::where('username', 'aiku')->get();
        $content = [
            'title' => 'Subject/Title',
            'body'  => 'Hello'
        ];

        $this->handle($users, $content);
    }
}
