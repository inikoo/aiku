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
use App\Transfers\Aurora\WithAuroraParsers;
use Illuminate\Support\Facades\DB;

class FetchAuroraWebBlockLink extends OrgAction
{

    use WithAuroraParsers;

    public function handle(Website $website, $auroraLink): array
    {
        print $auroraLink."\n";

        if (!$this->isInternalLink($website, $auroraLink)) {
            $linkData = [
                'type' => 'external',
                'link' => $auroraLink
            ];
        } else {
            $linkData = [
                'type'       => 'internal',
                'webpage_id' => null
            ];

            $auroraLink        = $this->cleanUrl($auroraLink);
            $auroraLink        = str_replace($website->domain, '', $auroraLink);
            $auroraLink        = preg_replace('/^\/+/', "", $auroraLink);
            $dataSource        = explode(':', $website->source_id);
            $auroraWebpageData = DB::connection('aurora')
                ->table('Page Store Dimension')
                ->select('Page Key as source_id')
                ->where('Webpage Website Key', $dataSource[1])
                ->where('Webpage Code', $auroraLink)
                ->orWhere('Webpage Canonical Code', $auroraLink)
                ->first();


            $linkedWebpage = $this->parseWebpage($website->organisation_id.':'.$auroraWebpageData->source_id);

            if ($linkedWebpage) {
                data_set($linkData, 'webpage_id', $linkedWebpage->id);
            }
        }


        return $linkData;
    }

    public function isInternalLink($website, $auroraLink): bool
    {
        if (!str_starts_with($auroraLink, "http")) {
            return true;
        }
        $domain       = $website->domain;
        $auroraLink   = $this->cleanUrl($auroraLink);
        $auroraDomain = preg_replace('/\/.*$/', "", $auroraLink);

        return $domain == $auroraDomain;
    }

    public function cleanUrl($auroraLink): string
    {
        $auroraLink = preg_replace('/^https?:\/\//', "", $auroraLink);
        $auroraLink = preg_replace('/^www\./', "", $auroraLink);

        $auroraLink = preg_replace('/^\/+/', "", $auroraLink);
        $auroraLink = preg_replace('/\/+$/', "", $auroraLink);

        return trim($auroraLink);
    }


}
