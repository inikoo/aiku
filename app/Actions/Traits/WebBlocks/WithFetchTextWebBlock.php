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
            data_set($layout, "data.fieldValue.value", $text);
            $this->replaceAnchor($webpage, $text);
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

    private function replaceAnchor(Webpage $webpage, &$text): void
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

            // TODO: change anchor tag to be <CustomLinkExtension type="internal" workshop="https://tailwindcss.com/docs/z-index" id="1" url="https://tailwindcss.com/docs/z-index">link test </CustomLinkExtension>
            foreach ($links as $index => $link) {
                $originalLink = FetchAuroraWebBlockLink::run($webpage->website, $link, $this->dbSuffix);
                $additionalAttribute = '';
                if ($originalLink['type'] == 'internal') {
                    $workshopRoute = $originalLink['workshop_route'];
                    $workshopUrl = route($workshopRoute['name'], $workshopRoute['parameters']);
                    $additionalAttribute .=
                        sprintf(
                            'id="%s" workshop="%s"',
                            $originalLink['webpage_id'],
                            $workshopUrl,
                        );
                }

                $customeExtensionElement = sprintf(
                    '<CustomeExtension url="%s" type="%s" %s %s %s>%s</CustomeExtension>',
                    $originalLink['url'],
                    $originalLink['type'],
                    $additionalAttribute,
                    $attributeBeforeHref[$index],
                    $attributeAfterHref[$index],
                    $textInsideAnchor[$index],
                );
                // dd($customeExtensionElement);

                $text = str_replace($originalAnchor[$index], $customeExtensionElement, $text);
            }
        }
    }
}
