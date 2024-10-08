<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 08 Oct 2024 10:02:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\SysAdmin\Organisation;

trait WithAuroraProcessWebpage
{
    public function processAuroraWebpage(Organisation $organisation,$auroraModelData): array|null
    {
        $website = $this->parseWebsite($organisation->id.':'.$auroraModelData->{'Webpage Website Key'});

        if ($website->state == WebsiteStateEnum::CLOSED) {
            $status = WebpageStateEnum::CLOSED;
        } elseif ($website->state == WebsiteStateEnum::IN_PROCESS) {
            $status = match ($auroraModelData->{'Webpage State'}) {
                'Online' => WebpageStateEnum::READY,
                'Offline' => WebpageStateEnum::CLOSED,
                default => WebpageStateEnum::IN_PROCESS,
            };
        } else {
            $status = match ($auroraModelData->{'Webpage State'}) {
                'Online' => WebpageStateEnum::LIVE,
                'Offline' => WebpageStateEnum::CLOSED,
                default => WebpageStateEnum::IN_PROCESS,
            };
        }


        $url = $this->cleanWebpageCode($auroraModelData->{'Webpage Code'});


        $purpose = match ($auroraModelData->{'Webpage Scope'}) {
            'Homepage', 'HomepageLogout', 'HomepageToLaunch' => WebpagePurposeEnum::STOREFRONT,
            'Asset' => WebpagePurposeEnum::PRODUCT,
            'Category Products' => WebpagePurposeEnum::FAMILY,
            'Category Categories' => WebpagePurposeEnum::DEPARTMENT,
            'Register' => WebpagePurposeEnum::REGISTER,
            'Login', 'ResetPwd' => WebpagePurposeEnum::LOGIN,
            'TandC' => WebpagePurposeEnum::TERMS_AND_CONDITIONS,
            'Basket', 'Top_Up', 'Checkout' => WebpagePurposeEnum::SHOPPING_CART,
            default => WebpagePurposeEnum::CONTENT,
        };

        $type = match ($auroraModelData->{'Webpage Scope'}) {
            'Homepage', 'HomepageLogout', 'HomepageToLaunch' => WebpageTypeEnum::STOREFRONT,
            'Asset', 'Category Categories', 'Category Products' => WebpageTypeEnum::SHOP,
            'Register', 'Login', 'ResetPwd' => WebpageTypeEnum::AUTH,
            'TandC' => WebpageTypeEnum::SMALL_PRINT,
            'Basket', 'Top_Up', 'Checkout' => WebpageTypeEnum::CHECKOUT,
            default => WebpageTypeEnum::CONTENT,
        };


        $model = null;
        if ($auroraModelData->{'Webpage Scope'} == 'Category Products') {
            $model = $this->parseFamily($organisation->id.':'.$auroraModelData->{'Webpage Scope Key'});
            if (!$model) {
                return null;
            }
        } elseif ($auroraModelData->{'Webpage Scope'} == 'Product') {
            $model = $this->parseProduct($organisation->id.':'.$auroraModelData->{'Webpage Scope Key'});
            if (!$model) {
                dd($auroraModelData->{'Webpage Scope Key'});
            }
        }


        $migrationData = null;
        if ($auroraModelData->{'Page Store Content Published Data'}) {
            $migrationData = json_decode($auroraModelData->{'Page Store Content Published Data'}, true);
        }

        $webpage =
            [
                'code'            => $url,
                'url'             => strtolower($url),
                'state'           => $status,
                'purpose'         => $purpose,
                'type'            => $type,
                'fetched_at'      => now(),
                'last_fetched_at' => now(),
                'source_id'       => $organisation->id.':'.$auroraModelData->{'Page Key'},

            ];

        if ($migrationData) {
            $webpage['migration_data'] = $migrationData;
        }

        if ($createdAt = $this->parseDate($auroraModelData->{'Webpage Creation Date'})) {
            $webpage['created_at'] = $createdAt;
        }

        if ($model) {
            $webpage['model_type'] = class_basename($model);
            $webpage['model_id']   = $model->id;
        }

        return [
            'website' => $website,
            'webpage' => $webpage
        ];
    }


}