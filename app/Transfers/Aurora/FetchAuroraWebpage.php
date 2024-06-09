<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraWebpage extends FetchAurora
{
    public function fetch(int $id): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        $this->parseModel();

        return $this->parsedData;
    }

    protected function parseModel(): void
    {


        $typeData=DB::connection('aurora')
            ->table('Webpage Type Dimension')
            ->select('Webpage Type Code')
            ->where('Webpage Type Key', $this->auroraModelData->{'Webpage Type Key'})->first();


        if($typeData and $typeData->{'Webpage Type Code'}=='Sys') {
            return;
        }


        if (preg_match('/\.sys$/', $this->auroraModelData->{'Webpage Code'})) {
            return;
        }
        if (preg_match('/^web\./i', $this->auroraModelData->{'Webpage Code'})) {
            return;
        }

        if (preg_match('/^fam\./i', $this->auroraModelData->{'Webpage Code'})) {
            return;
        }

        if (preg_match('/^dept\./i', $this->auroraModelData->{'Webpage Code'})) {
            return;
        }

        if (preg_match('/blog/i', $this->auroraModelData->{'Webpage Code'})) {
            return;
        }



        $this->parsedData['website'] = $this->parseWebsite($this->organisation->id.':'.$this->auroraModelData->{'Webpage Website Key'});


        if ($this->parsedData['website']->state == WebsiteStateEnum::CLOSED) {
            $status = WebpageStateEnum::CLOSED;
        } elseif ($this->parsedData['website']->state == WebsiteStateEnum::IN_PROCESS) {
            $status = match ($this->auroraModelData->{'Webpage State'}) {
                'Online'  => WebpageStateEnum::READY,
                'Offline' => WebpageStateEnum::CLOSED,
                default   => WebpageStateEnum::IN_PROCESS,
            };
        } else {
            $status = match ($this->auroraModelData->{'Webpage State'}) {
                'Online'  => WebpageStateEnum::LIVE,
                'Offline' => WebpageStateEnum::CLOSED,
                default   => WebpageStateEnum::IN_PROCESS,
            };
        }


        $url = $this->cleanWebpageCode($this->auroraModelData->{'Webpage Code'});


        $purpose = match ($this->auroraModelData->{'Webpage Scope'}) {
            'Homepage', 'HomepageLogout', 'HomepageToLaunch' => WebpagePurposeEnum::STOREFRONT,
            'Asset'                => WebpagePurposeEnum::PRODUCT_OVERVIEW,
            'Category Products'    => WebpagePurposeEnum::PRODUCT_LIST,
            'Category Categories'  => WebpagePurposeEnum::CATEGORY_PREVIEW,
            'Register'             => WebpagePurposeEnum::REGISTER,
            'Login', 'ResetPwd' => WebpagePurposeEnum::LOGIN,
            'TandC' => WebpagePurposeEnum::TERMS_AND_CONDITIONS,
            'Basket', 'Top_Up', 'Checkout' => WebpagePurposeEnum::SHOPPING_CART,
            default => WebpagePurposeEnum::CONTENT,
        };


        $type = match ($this->auroraModelData->{'Webpage Scope'}) {
            'Homepage', 'HomepageLogout', 'HomepageToLaunch' => WebpageTypeEnum::STOREFRONT,
            'Asset', 'Category Categories', 'Category Products' => WebpageTypeEnum::SHOP,
            'Register', 'Login', 'ResetPwd' => WebpageTypeEnum::AUTH,
            'TandC' => WebpageTypeEnum::SMALL_PRINT,
            'Basket', 'Top_Up', 'Checkout' => WebpageTypeEnum::CHECKOUT,
            default => WebpageTypeEnum::CONTENT,
        };


        $this->parsedData['webpage'] =
            [


                'code'    => $url,
                'url'     => strtolower($url),
                'state'   => $status,
                'purpose' => $purpose,
                'type'    => $type,


                'source_id' => $this->organisation->id.':'.$this->auroraModelData->{'Page Key'},

            ];
        if ($createdAt = $this->parseDate($this->auroraModelData->{'Webpage Creation Date'})) {
            $this->parsedData['webpage']['created_at'] = $createdAt;
        }


    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Page Store Dimension')
            ->where('aiku_ignore', 'No')
            ->where('Page Key', $id)->first();
    }
}
