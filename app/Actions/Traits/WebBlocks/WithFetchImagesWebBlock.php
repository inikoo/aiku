<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 09-10-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
 */

namespace App\Actions\Traits\WebBlocks;

use App\Actions\Transfers\Aurora\FetchAuroraWebBlockLink;
use App\Models\Web\Webpage;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchImagesWebBlock
{
    use AsAction;

    public function processImagesData(Webpage $webpage, $auroraBlock): array|null
    {
        if (!isset($auroraBlock["images"])) {
            return null;
        }
        $layoutType = $this->getImagesLayoutTypeByReso($auroraBlock);

        if ($layoutType) {
            data_set($layout, "data.fieldValue.value.layout_type", $layoutType);
        }

        $imagesArray = [];

        foreach ($auroraBlock["images"] as $image) {
            if (isset($image["link"])) {
                $linksData = FetchAuroraWebBlockLink::run($webpage->website, $image["link"], $this->dbSuffix);
            }

            if (!isset($image["src"])) {
                continue;
            }
            $imagesArray[] = [
                "link_data"     => $linksData ?? null,
                "aurora_source" => $image["src"],
            ];
        }

        data_set($layout, "data.fieldValue.value.row", $imagesArray);

        return $layout;
    }

    public function getImagesLayoutTypeByReso($auroraBlock): string|null
    {
        $images = $auroraBlock['images'];

        $widths = array_column($images, 'width'); // Get all widths in the current set
        // print_r($widths);
        $totalWidth = array_sum($widths); // Sum of all widths to calculate ratios

        // Calculate the ratios of each image width to the total width
        $ratios = array_map(
            function ($width) use ($totalWidth) {
                return round($width / $totalWidth, 2);
            },
            $widths
        );

        $code = '';
        switch (count($ratios)) {
            case 1:
                $code = '1';
                break;
            case 2:
                if ($ratios[0] == 0.50 && $ratios[1] == 0.50) {
                    $code = '2';
                } elseif ($ratios[0] == 0.33 && $ratios[1] == 0.67) {
                    $code = '12';
                } elseif ($ratios[0] == 0.67 && $ratios[1] == 0.33) {
                    $code = '21';
                } elseif ($ratios[0] == 0.25 && $ratios[1] == 0.75) {
                    $code = '13';
                } elseif ($ratios[0] == 0.75 && $ratios[1] == 0.25) {
                    $code = '31';
                }
                break;
            case 3:
                if ($ratios[0] == 0.33 && $ratios[1] == 0.33 && $ratios[2] == 0.33) {
                    $code = '3';
                } elseif ($ratios[0] == 0.50 && $ratios[1] == 0.25 && $ratios[2] == 0.25) {
                    $code = '211';
                }
                break;
            case 4:
                if ($ratios[0] == 0.25 && $ratios[1] == 0.25 && $ratios[2] == 0.25 && $ratios[3] == 0.25) {
                    $code = '4';
                }
                break;
        }

        return $code ?? null;
    }

}
