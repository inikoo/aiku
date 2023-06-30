<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress;

use App\Models\Notifications\SimpleNotification;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetNotificationSnsEmailAddress
{
    use AsAction;

    public mixed $message;

    public function handle(mixed $request): \Illuminate\Http\RedirectResponse
    {
        $snsMessage = json_decode($request, true);

        SimpleNotification::create([
            'notification_id' => $snsMessage['SubscribeURL'],
            'data' => $request
        ]);

//        return redirect()->away($snsMessage['SubscribeURL']);
    }

    public function asController(ActionRequest $request)
    {
        return 'hello';
//        return $this->handle($request->getContent());
    }
}
