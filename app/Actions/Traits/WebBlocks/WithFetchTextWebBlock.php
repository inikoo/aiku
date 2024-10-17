<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 09 Oct 2024 12:36:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\WebBlocks;

use App\Actions\Transfers\Aurora\FetchAuroraWebBlockLink;
use App\Models\Web\Webpage;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchTextWebBlock
{
    use AsAction;
    public function processTextData(Webpage $webpage, $auroraBlock): array|null
    {
        $text = $auroraBlock["text_blocks"];
        if (count($text) > 0) {
            $text = $text[0]['text'] ?? null;
            data_set($layout, "data.fieldValue.value", $text);
        }
        $pattern = '/<a\s+[^>]*href=["\']([^"\']*)["\'][^>]*>/i'; // just link for the 'a' tag
        preg_match_all($pattern, $text, $matches);
        $links = $matches[1];

        $linksData = [];
        foreach ($links as $link) {
            $linksData[] = FetchAuroraWebBlockLink::run($webpage->website, $link);
        }

        data_set($layout, "data.fieldValue.link_data", $linksData, false);
        return $layout ?? null;
    }

    public function processPhoneData($auroraBlock): array
    {
        $text   = $auroraBlock['_text'];
        $title = $auroraBlock['_title'];
        $telephone = $auroraBlock['_telephone'];

        $cleanedTitle = str_replace('&nbsp;', ' ', $title);
        $cleanedTelephone = str_replace([' ', '(0)', ' '], '', $telephone);

        $html = '<p>' . $text . '</p>';
        $html .= '<p style="text-align: center"><strong>' . $cleanedTitle . '</strong></p>';
        $html .= '<h1 style="text-align: center"><mark data-color="#A9FF00" style="background-color: #A9FF00; color: inherit">' . $cleanedTelephone . '</mark></h1>';

        data_set($layout, "data.fieldValue.value", $html);

        return $layout;
    }
}
