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
        // TODO: discuss with arya is this correct replacement
        $text = $auroraBlock["text_blocks"];
        if (count($text) > 0) {
            $text = $text[0]['text'] ?? null;
            $this->replaceAnchor($webpage, $text, $layout);
            data_set($layout, "data.fieldValue.value", $text);
        }
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

    private function replaceAnchor(Webpage $webpage, &$text, &$layout): void
    {
        if ($text) {
            $patternAnchors = "/<a\s([^>]*?)href=['\"](.*?)['\"]([^>]*)>(.*?)<\/a>/i";
            preg_match_all($patternAnchors, $text, $matches);
            $originalAnchor = $matches[0];
            $links = $matches[2];

            $attributeBeforeHref = $matches[1];
            $attributeAfterHref = $matches[3];
            $textInsideAnchor = $matches[4];

            if (!$links) {
                return;
            }

            $externalLinks = [];
            foreach ($links as $index => $link) {
                $originalLink = FetchAuroraWebBlockLink::run($webpage->website, $link, $this->dbSuffix);
                $additionalAttribute = '';
                if ($originalLink['type'] == 'internal') {
                    $additionalAttribute .=
                        sprintf(
                            'id="%s" workshop="%s"',
                            $originalLink['id'],
                            $originalLink['workshop_url'],
                        );
                } else {
                    $externalLinks[] = $originalLink['url'];
                }

                $customeExtensionElement = sprintf(
                    '<a href="%s" type="%s" %s %s %s>%s</a>',
                    $originalLink['url'],
                    $originalLink['type'],
                    $additionalAttribute,
                    $attributeBeforeHref[$index],
                    $attributeAfterHref[$index],
                    $textInsideAnchor[$index],
                );

                $text = str_replace($originalAnchor[$index], $customeExtensionElement, $text);
            }
            $layout['external_links'] = $externalLinks;
        }
    }
}
