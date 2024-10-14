<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 08 Oct 2024 10:02:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Web\Webpage\WebpageSubTypeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\Catalogue\ProductCategory;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Facades\DB;

trait WithAuroraProcessWebpage
{
    public function processAuroraWebpage(Organisation $organisation, $auroraModelData): array|null
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

        $subType = match ($auroraModelData->{'Webpage Scope'}) {
            'Homepage', 'HomepageLogout', 'HomepageToLaunch' => WebpageSubTypeEnum::STOREFRONT,
            'Product' => WebpageSubTypeEnum::PRODUCT,
            'Category Products' => WebpageSubTypeEnum::FAMILY,
            'Category Categories' => WebpageSubTypeEnum::DEPARTMENT,
            'Register' => WebpageSubTypeEnum::REGISTER,
            'Login', 'ResetPwd' => WebpageSubTypeEnum::LOGIN,
            'TandC' => WebpageSubTypeEnum::TERMS_AND_CONDITIONS,
            'Basket', 'Top_Up', => WebpageSubTypeEnum::BASKET,
            'Checkout' => WebpageSubTypeEnum::CHECKOUT,
            default => WebpageSubTypeEnum::CONTENT,
        };

        $type = match ($auroraModelData->{'Webpage Scope'}) {
            'Homepage', 'HomepageLogout', 'HomepageToLaunch' => WebpageTypeEnum::STOREFRONT,
            'Product', 'Category Categories', 'Category Products' => WebpageTypeEnum::CATALOGUE,
            'Register', 'Login', 'ResetPwd', 'Basket', 'Top_Up', 'Checkout' => WebpageTypeEnum::OPERATIONS,
            'TandC' => WebpageTypeEnum::INFO,
            default => WebpageTypeEnum::CONTENT,
        };


        $model = null;


        if ($auroraModelData->{'Webpage Scope'} == 'Category Products') {
            $model = $this->parseFamily($organisation->id.':'.$auroraModelData->{'Webpage Scope Key'});
            if (!$model) {
                return null;
            }
        } elseif ($auroraModelData->{'Webpage Scope'} == 'Category Categories') {
            $model = $this->parseDepartment($organisation->id.':'.$auroraModelData->{'Webpage Scope Key'});
            if (!$model) {
                return null;
            }
        } elseif ($auroraModelData->{'Webpage Scope'} == 'Product') {
            $model = $this->parseProduct($organisation->id.':'.$auroraModelData->{'Webpage Scope Key'});
            if (!$model) {
                return null;
            }
        }


        $migrationData = null;
        if ($auroraModelData->{'Page Store Content Published Data'}) {
            $migrationData = json_decode($auroraModelData->{'Page Store Content Published Data'}, true);
        }

        // print ">>> $url <<<\n";

        $title = trim($auroraModelData->{'Webpage Name'});
        if ($title == '') {
            $title = $auroraModelData->{'Webpage Code'};
        }


        switch ($type) {
            case WebpageTypeEnum::CATALOGUE:
                if ($subType == WebpageSubTypeEnum::PRODUCT) {
                    $parentId = $website->products_id;
                } elseif ($subType == WebpageSubTypeEnum::FAMILY) {
                    $parentId = $website->catalogue_id;
                    /** @var ProductCategory $department */
                    $department = $model->department;
                    if ($department) {
                        $departmentSourceData        = explode(':', $department->source_department_id);
                        $auroraDepartmentWebpageData = DB::connection('aurora')->table('Page Store Dimension')
                            ->select('Page Key')
                            ->where('Webpage Scope', 'Category Categories')
                            ->where('Webpage Scope Key', $departmentSourceData[1])
                            ->first();
                        if ($auroraDepartmentWebpageData) {
                            $departmentWebpage = $this->parseWebpage($this->organisation->id.':'.$auroraDepartmentWebpageData->{'Page Key'});


                            if ($departmentWebpage) {
                                $parentId = $departmentWebpage->id;
                            } else {
                                print "error can not fetch department webpage\n";
                            }
                        }
                    }
                } else {
                    $parentId = $website->catalogue_id;
                }

                break;
            default:
                $parentId = $website->storefront_id;
                break;
        }


        $webpage =
            [
                'parent_id'       => $parentId,
                'code'            => $url,
                'title'           => $title,
                'description'     => (string)$auroraModelData->{'Webpage Meta Description'},
                'url'             => strtolower($url),
                'state'           => $status,
                'sub_type'        => $subType,
                'type'            => $type,
                'fetched_at'      => now(),
                'last_fetched_at' => now(),
                'source_id'       => $organisation->id.':'.$auroraModelData->{'Page Key'},

            ];

        if ($migrationData) {
            if ($auroraModelData->{'Webpage Code'} == 'home.sys') {
                $webpage['migration_data'] = [
                    'loggedIn' => $migrationData
                ];
            } elseif ($auroraModelData->{'Webpage Code'} == 'home_logout.sys') {
                $webpage['migration_data'] = [
                    'loggedOut' => $migrationData
                ];
            } else {
                $webpage['migration_data'] = [
                    'both' => $migrationData
                ];
            }
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
