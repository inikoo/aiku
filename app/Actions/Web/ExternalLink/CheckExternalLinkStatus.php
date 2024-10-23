<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 23-10-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\ExternalLink;

use App\Actions\OrgAction;
use App\Rules\AlphaDashSlash;

class CheckExternalLinkStatus extends OrgAction
{
    public function handle(string $url, int $retries = 3): string
    {
        if ($retries <= 0) {
            return '408'; // timeout
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 0) {
            return $this->handle($url, $retries - 1);
        }

        return $httpCode;
    }

    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'ascii',
                'lowercase',
                'max:255',
                new AlphaDashSlash(),
            ]
        ];
    }
}
