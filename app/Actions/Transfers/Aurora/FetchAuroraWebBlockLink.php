<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 17-10-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Actions\Transfers\Aurora;

use App\Actions\OrgAction;
use App\Models\Web\Website;
use App\Transfers\Aurora\WithAuroraImages;

class FetchAuroraWebBlockLink extends OrgAction
{
    // use WithAuroraImages;

    public function handle(Website $website, $auroraLink)
    {

        return $this->isInternal($website, $auroraLink);
    }

    public function isInternal($website, $auroraLink)
    {
        if (!str_starts_with($auroraLink, "http")) {
            return true;
        }
        $domain = $website->domain;
        $auroraLink = preg_replace('/^https?:\/\//', "", $auroraLink);
        $auroraLink = preg_replace('/^www\./', "", $auroraLink);
        $auroraDomain = preg_replace('/\/.*$/', "", $auroraLink);
        return $domain == $auroraDomain;
    }




}
