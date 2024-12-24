<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 24 Dec 2024 12:48:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Utils;

use Lorisleiva\Actions\Concerns\AsAction;

class GetOsFromUserAgent
{
    use AsAction;

    public function handle($parsedUserAgent): string
    {

        // if platform=='Windows 10' need to check if it is actually Windows 11 see:
        // https://developers.whatismybrowser.com/learn/browser-detection/client-hints/detect-windows-11-client-hints
        // https://stackoverflow.com/questions/68614445/how-to-detect-windows-11-from-user-agent

        if ($parsedUserAgent->isWindows()) {
            if (str_contains($parsedUserAgent->userAgent(), 'Windows NT 10.0; Win64; x64')) {
                return 'Windows 11';
            }

            return 'Windows 10';
        }

        return $parsedUserAgent->platformName() ?: 'Unknown';
    }
}
