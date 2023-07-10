<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 05 Jul 2023 13:43:20 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Google\Drive;

use Lorisleiva\Actions\Concerns\AsAction;

class GetAuthCodeGoogleDrive {
    use AsAction;

    public function handle(string $url): string
    {
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $query);

        return $query['code'];
    }
}
