<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Firebase;

use App\Models\Notifications\FcmToken;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsObject;

class StoreFirebaseCloudMessagingToken
{
    use AsObject;
    use AsAction;

    public function handle(ActionRequest $request): void
    {
        Organisation::where('slug', 'aw')->first()->makeCurrent();

        $token = FcmToken::firstOrNew([
            'token_id' => $request->user()->currentAccessToken()->token
        ]);

        $token->fcm_token = $request->input('token');

        $request->user()->fcmToken()->save($token);
    }

    public function asController(ActionRequest $request): void
    {
        $this->handle($request);
    }
}
