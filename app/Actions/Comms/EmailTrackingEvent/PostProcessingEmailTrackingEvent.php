<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailTrackingEvent;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Comms\EmailTrackingEvent;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Support\Arr;

class PostProcessingEmailTrackingEvent extends OrgAction
{
    use WithNoStrictRules;
    use WithActionUpdate;

    public function handle(EmailTrackingEvent $emailTrackingEvent): EmailTrackingEvent
    {
        $parsedUserAgent = (new Browser())->parse(Arr::get($emailTrackingEvent->data, 'userAgent'));

        $ip = Arr::get($emailTrackingEvent->data, 'ipAddress');
        $device = $parsedUserAgent->deviceType();

        return $this->update($emailTrackingEvent, [
            'ip' => $ip,
            'device' => $device,
            'data' => (object) []
       ]);
    }
}
