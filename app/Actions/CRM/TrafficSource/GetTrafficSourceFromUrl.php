<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 23 Jul 2025 12:42:09 British Summer Time, Trnava, Slovakia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\TrafficSource;

use App\Enums\CRM\TrafficSource\TrafficSourcesTypeEnum;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTrafficSourceFromUrl
{
    use AsAction;

    /**
     * The utm_medium values that mark a Meta click as bought, compared without case: ads are tagged
     * by hand in each account, so `paid`, `Ads` and `cpc` all turn up for the same kind of click.
     */
    public const array META_PAID_MEDIUMS = ['paid', 'ads', 'ad', 'cpc', 'paid_social', 'paidsocial', 'paid-social'];

    public function handle($url): ?string
    {
        $urlComponents = parse_url($url);
        $queryParams   = [];

        if (isset($urlComponents['query'])) {
            parse_str($urlComponents['query'], $queryParams);
        }

        /* gclid alone is enough to prove a paid Google click: custom tracking templates and some
           placement types omit the gad_* parameters, and demanding all three sent that paid traffic
           into organic via the referer fallback. The campaign id rides along when present. */
        if (array_key_exists('gclid', $queryParams)) {
            return
                TrafficSourcesTypeEnum::abbr()[TrafficSourcesTypeEnum::GOOGLE_ADS->value].
                $this->sanitizeCampaignRef(Arr::get($queryParams, 'gad_campaignid'));
        }

        if (array_key_exists('fbclid', $queryParams) && in_array(strtolower(trim((string) Arr::get($queryParams, 'utm_medium'))), self::META_PAID_MEDIUMS, true)) {
            /* Instagram ads are bought in the same Meta ad account as Facebook ones, so the click id
               cannot tell them apart; Meta's `{{site_source_name}}` macro can, and the ads already
               carry it as utm_source (`ig`, `fb`, `an`, `msg`, `th`). Anything that is not Instagram
               stays under Meta Ads, matching how the spend is split on the cost side. */
            $isInstagram = in_array(strtolower((string) Arr::get($queryParams, 'utm_source')), ['ig', 'instagram'], true);
            $campaignRef = $this->metaCampaignRef($queryParams);

            if ($isInstagram) {
                return
                    TrafficSourcesTypeEnum::abbr()[TrafficSourcesTypeEnum::INSTAGRAM_ADS->value].
                    ($campaignRef === '' ? '' : TrafficSourcesTypeEnum::INSTAGRAM_CAMPAIGN_PREFIX.$campaignRef);
            }

            return TrafficSourcesTypeEnum::abbr()[TrafficSourcesTypeEnum::META_ADS->value].$campaignRef;
        }

        /* Bing carries no campaign id of its own: msclkid is unique per click, so recording it as a
           campaign made every Bing click a distinct campaign, over-weighting Bing in the share split
           and matching no imported cost row ever. The campaign only arrives when the ads are tagged
           with Microsoft's `utm_campaign={CampaignId}`, which is the same numeric id the cost script
           uploads, so anything non-numeric is a hand-written campaign name that would never meet a
           cost row and is dropped back to an unattributed Bing click. */
        if (array_key_exists('msclkid', $queryParams)) {
            $bingCampaignRef = $this->sanitizeCampaignRef(Arr::get($queryParams, 'utm_campaign'));

            return
                TrafficSourcesTypeEnum::abbr()[TrafficSourcesTypeEnum::BING_ADS->value].
                (ctype_digit($bingCampaignRef) ? $bingCampaignRef : '');
        }




        return null;
    }

    /**
     * Meta's campaign id, which the cost side keys campaigns on: `utm_campaign={{campaign.id}}` when
     * the ads are tagged that way, otherwise `utm_id={{campaign.id}}` beside a campaign name, as the
     * Ads Manager URL builder tags them. A name is kept only when no id is given.
     *
     * @param array<string, mixed> $queryParams
     */
    private function metaCampaignRef(array $queryParams): string
    {
        $campaign = $this->sanitizeCampaignRef(Arr::get($queryParams, 'utm_campaign'));
        $id       = $this->sanitizeCampaignRef(Arr::get($queryParams, 'utm_id'));

        return match (true) {
            ctype_digit($campaign) => $campaign,
            ctype_digit($id)       => $id,
            default                => $campaign,
        };
    }

    /**
     * The cookie is a pipe/comma-joined touch string, so a campaign name containing either separator
     * would shatter the whole history into garbage segments when parsed back.
     */
    private function sanitizeCampaignRef(?string $reference): string
    {
        return preg_replace('/[|,\s]+/', '-', trim((string) $reference)) ?? '';
    }
}
