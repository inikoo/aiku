<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 08 Oct 2024 16:16:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Web\WebBlock\DeleteWebBlock;
use App\Models\Catalogue\Product;
use App\Transfers\Aurora\WithAuroraParsers;
use Illuminate\Support\Str;
use App\Actions\Helpers\Images\GetPictureSources;
use App\Actions\OrgAction;
use App\Actions\Traits\WebBlocks\WithFetchCTA1WebBlock;
use App\Actions\Traits\WebBlocks\WithFetchFamilyWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchIFrameWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchOverviewWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchProductsWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchProductWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchScriptWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchSeeAlsoWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchTextWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchDepartmentWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchImagesWebBlock;
use App\Actions\Traits\WithOrganisationSource;
use App\Actions\Web\WebBlock\StoreWebBlock;
use App\Actions\Web\Webpage\UpdateWebpageContent;
use App\Events\BroadcastPreviewWebpage;
use App\Models\Catalogue\ProductCategory;
use App\Models\Web\Webpage;
use App\Transfers\AuroraOrganisationService;
use App\Transfers\WowsbarOrganisationService;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FetchAuroraWebBlocks extends OrgAction
{
    use WithAuroraParsers;
    use WithAuroraOrganisationsArgument;
    use WithOrganisationSource;
    use WithFetchTextWebBlock;
    use WithFetchImagesWebBlock;
    use WithFetchIFrameWebBlock;
    use WithFetchProductWebBlock;
    use WithFetchOverviewWebBlock;
    use WithFetchCTA1WebBlock;
    use WithFetchSeeAlsoWebBlock;
    use WithFetchProductsWebBlock;
    use WithFetchFamilyWebBlock;
    use WithFetchScriptWebBlock;
    use WithFetchDepartmentWebBlock;


    protected AuroraOrganisationService|WowsbarOrganisationService|null $organisationSource = null;

    /**
     * @throws \Exception
     */
    public function handle(Webpage $webpage, $reset = false, $dbSuffix = ''): Webpage
    {


        $this->organisationSource = $this->getOrganisationSource($webpage->organisation);
        $this->organisationSource->initialisation($webpage->organisation, $dbSuffix);


        if ($reset) {
            $this->reset($webpage);
        }


        $oldMigrationsChecksum = $webpage->webBlocks()->get()->pluck('migration_checksum', 'migration_checksum')->toArray();
        if (isset($webpage->migration_data)) {
            $migrationTypes = ['both', 'loggedIn', 'loggedOut'];

            $allMigrationsDataByType = [];
            foreach ($migrationTypes as $type) {
                if (isset($webpage->migration_data[$type])) {
                    if (isset($webpage->migration_data[$type]['blocks'])) {
                        $allMigrationsDataByType[$type] = $this->getMigrationData($webpage, $webpage->migration_data[$type]['blocks'], $type);
                    }
                }
            }

            // if(count($allMigrationData) > 1) {

            // }else {

            // }
            // $migrationsData = [];

            // foreach($allMigrationsDataByType as $type => $migrationDataByType) {
            //     foreach($migrationDataByType as $migrationData) {
            //         $migrationChecksum = $migrationData['migrationChecksum'];
            //         if(isset($migrationsData[$migrationChecksum])) {
            //             $migrationsData[$migrationChecksum]['visibility'][$type] = true; // make it both
            //             continue;
            //         }
            //         $migrationsData[$migrationChecksum] = $migrationData;
            //     }

            // }

            $migrationsData = [];
            foreach ($allMigrationsDataByType as $type => $migrationData) {
                foreach ($migrationData as $data) {
                    $checksum = $data['migrationChecksum'];
                    if ($type == 'both') {
                        $migrationsData[$checksum] = $data;
                        $migrationsData[$checksum]['visibility']['loggedIn'] = true;
                        $migrationsData[$checksum]['visibility']['loggedOut'] = true;
                        continue;
                    }
                    $isLoggedIn = $type == 'loggedIn';
                    if (!isset($migrationsData[$checksum])) {
                        $migrationsData[$checksum] = $data;
                        $migrationsData[$checksum]['visibility']['loggedIn'] = $isLoggedIn;
                        $migrationsData[$checksum]['visibility']['loggedOut'] = !$isLoggedIn;
                    } else {
                        $migrationsData[$checksum][$type] = true;
                    }
                }
            }
            // dd($migrationsData);

            $this->processMigrationsData($migrationsData, $oldMigrationsChecksum);
        }

        if (count($oldMigrationsChecksum) > 0) {
            $this->deleteWebBlockHaveOldChecksum($webpage, $oldMigrationsChecksum);
        }

        return $webpage;
    }

    private function getMigrationData($webpage, array $blocks, $type): array
    {
        $migrationData = [];
        foreach ($blocks as $index => $auroraBlock) {
            $migrationChecksum = md5(json_encode($auroraBlock));
            // print $migrationChecksum . " " . $auroraBlock['type'] . " " . $type ."\n";
            $loggedInStatus = $type === 'loggedIn';
            $migrationData[] = [
                'visibility' => [
                    'loggedIn' => $loggedInStatus,
                    'loggedOut' => !$loggedInStatus,
                ],
                'webpage' => $webpage,
                'auroraBlock' => $auroraBlock,
                'position' => $index + 1,
                'migrationChecksum' => $migrationChecksum,
            ];

        }
        // dd($migrationData);
        return $migrationData;
    }

    private function processMigrationsData(array $migrationsData, array &$oldMigrationsChecksum): void
    {
        // $newPosition = 1;
        // foreach($migrationsData as $migrationChecksum => $migrationData) {
        //     if (isset($oldMigrationsChecksum[$migrationChecksum])) {
        //         dd($migrationData['position'], $migrationsData[$migrationChecksum]['position']);
        //         $modelHasWebBlocks = DB::table("model_has_web_blocks")->where('migration_checksum', $migrationChecksum);
        //         $modelHasWebBlocks->update(['position' => $migrationData['position']]);
        //         unset($oldMigrationsChecksum[$migrationChecksum]);
        //         continue;
        //     }
        //     // $newPosition++;
        //     // dd("kena", $oldMigrationsChecksum[$migrationChecksum] ?? null);
        //     $this->processData($migrationData['webpage'], $migrationData['auroraBlock'], $migrationData['migrationChecksum'],$migrationData['position'], $migrationData['visibility']);
        // }

        $newPosition = 1;
        foreach ($migrationsData as $checksum => $migrationData) {
            if (isset($oldMigrationsChecksum[$checksum])) {
                $modelHasWebBlocks = DB::table("model_has_web_blocks")->where('migration_checksum', $checksum);
                $modelHasWebBlocks->update(['position' => $newPosition]);
                unset($oldMigrationsChecksum[$checksum]);
            } else {
                $this->processData($migrationData['webpage'], $migrationData['auroraBlock'], $migrationData['migrationChecksum'], $newPosition, $migrationData['visibility']);
            }
            $newPosition++;
        }
    }

    private function processData(
        Webpage $webpage,
        $auroraBlock,
        $migrationChecksum,
        int $position,
        $visibility = ["loggedIn" => true, "loggedOut" => true]
    ): void {
        $models = [];
        $group = $webpage->group;


        // if($auroraBlock['type'] != "text") {
        //     return;
        // }
        switch ($auroraBlock["type"]) {
            case "images":
                $webBlockType = $group->webBlockTypes()->where("code", "images")->first();
                $layout       = $this->processImagesData($webpage, $auroraBlock);
                break;
            case "text":
                $webBlockType = $group->webBlockTypes()->where("slug", "text")->first();
                $layout       = $this->processTextData($auroraBlock);
                break;
            case "telephone":
                $webBlockType = $group->webBlockTypes()->where("slug", "text")->first();
                $layout       = $this->processPhoneData($auroraBlock);
                break;
            case "code":
            case "reviews":
                $webBlockType = $group->webBlockTypes()->where("slug", "script")->first();
                $layout       = $this->processScriptData($auroraBlock);
                break;
            case "map":
            case "iframe":
                $webBlockType = $group->webBlockTypes()->where("slug", "iframe")->first();
                $layout       = $this->processIFrameData($auroraBlock);
                break;
            case "product":
                $webBlockType = $group->webBlockTypes()->where("slug", "product")->first();
                $layout       = $this->processProductData($auroraBlock);
                $models[]     = Product::find($webpage->model_id);
                break;

            case "category_products":
                $webBlockType = $group->webBlockTypes()->where("slug", "family")->first();
                $models[]     = ProductCategory::find($webpage->model_id);
                $layout       = $this->processFamilyData($webpage, $auroraBlock);
                break;

            case "see_also":
                $webBlockType = $group->webBlockTypes()->where("slug", "see_also")->first();
                $productsId   = [];
                $categoriesId = [];
                foreach ($auroraBlock["items"] as $item) {
                    if ($item['type'] == "product") {
                        if ($item['product_id']) {
                            $productsId[] = $item['product_id'];
                        }
                    }
                    if ($item['type'] == 'category') {
                        $categoriesId[] = $item['category_key'];
                    }
                }

                foreach ($productsId as $productId) {
                    $product = $this->parseProduct($webpage->organisation->id.':'.$productId);
                    if ($product) {
                        $models[] = $product;
                    }
                }

                foreach ($categoriesId as $categoryId) {
                    $family = $this->parseFamily($webpage->organisation->id.':'.$categoryId);
                    if ($family) {
                        $models[] = $family;
                    } else {
                        $department = $this->parseDepartment($webpage->organisation->id.':'.$categoryId);
                        if ($department) {
                            $models[] = $department;
                        }
                    }
                }


                $layout = $this->processSeeAlsoData();
                break;

            case "products":
                $webBlockType = $group->webBlockTypes()->where("slug", "products")->first();
                $productsId   = [];
                foreach ($auroraBlock["items"] as $item) {
                    if ($item['type'] == "product") {
                        $productsId[] = $item['product_id'];
                    }
                }
                if (count($productsId) > 0) {
                    foreach ($productsId as $productId) {
                        $product = $this->parseProduct($webpage->organisation->id.':'.$productId);
                        if ($product) {
                            $models[] = $this->parseProduct($webpage->organisation->id.':'.$productId);
                        }
                    }
                }
                $layout = $this->processProductsData($auroraBlock);
                break;

            case "category_categories":
                $webBlockType = $group->webBlockTypes()->where("slug", "department")->first();
                $layout       = $this->processDepartmentData($models, $webpage, $auroraBlock);
                break;

            case "blackboard":
                $webBlockType = $group->webBlockTypes()->where("slug", "overview")->first();
                $layout       = $this->processOverviewData($auroraBlock);
                break;
            case "button":
                $webBlockType = $group->webBlockTypes()->where("slug", "cta1")->first();
                $layout       = $this->processCTA1Data($auroraBlock);
                break;
            default:
                print ">>>>> ".$webpage->slug."  ".$auroraBlock["type"]."  <<<<<<\n";

                return;
        }

        if ($layout == null) {
            return;
        }

        // add fixed value to show the component can editable or not
        // data_set($layout, 'fixed', false, false);

        // $defaultPropertiesFromJson = Arr::get($webBlockType->data, 'properties');
        // dd($webBlockType->blueprint);
        // if ($defaultPropertiesFromJson) {
        //     data_set($layout, "properties", $defaultPropertiesFromJson);
        // } else {
        // }
        data_set($layout, "data.properties.padding.unit", "px");
        data_set($layout, "data.properties.padding.left.value", 20);
        data_set($layout, "data.properties.padding.right.value", 20);
        data_set($layout, "data.properties.padding.bottom.value", 20);
        data_set($layout, "data.properties.padding.top.value", 20);
        data_set($layout, "data.properties.padding.top.value", 20);
        // dd($layout);

        $webBlock = StoreWebBlock::make()->action(
            $webBlockType,
            [
                "layout"             => $layout,
                "migration_checksum" => $migrationChecksum,
                "models"             => $models,
            ],
            strict: false
        );

        if (
            $webBlock->webBlockType->code == "images"
            || $webBlock->webBlockType->code == "overview"
            || $webBlock->webBlockType->code == "cta1"
            || $webBlock->webBlockType->code == "family"
            || $webBlock->webBlockType->code == "department"
        ) {
            $code = $webBlock->webBlockType->code;

            if ($code == "family") {
                $items  = $webBlock->layout['data']["fieldValue"]["value"]["items"];
                $addOns = [];
                foreach ($items as $item) {
                    if ($item['type'] == "image") {
                        $imageSource = $this->processImage($webBlock, $item, $webpage);
                        $addOns[]    = ['position' => $item['position'], "type" => $item['type'], "source" => $imageSource];
                    } else {
                        $addOns[] = $item;
                    }
                }
                data_set($layout, "data.fieldValue.value.addOns", $addOns);
                unset($layout['data']["fieldValue"]["value"]["items"]);
            } elseif ($code == "department") {
                $sections = $webBlock->layout['data']["fieldValue"]["value"]["sections"];
                foreach ($sections as $sectionPosition => $section) {
                    $items = $section['items'];
                    if ($items) {
                        foreach ($items as $index => $item) {
                            if ($item['type'] == "image") {
                                $imageSource             = $this->processImage($webBlock, $item, $webpage);
                                $items[$index]["source"] = $imageSource;
                                unset($items[$index]["aurora_source"]);
                            }
                        }
                        $sections[$sectionPosition]["items"] = $items;
                    }
                }
                data_set($layout, "data.fieldValue.value.sections", $sections);
            } else {
                foreach ($layout['data']["fieldValue"]["value"] as $index => $imageRawData) {
                    $imageSource    = $this->processImage($webBlock, $imageRawData, $webpage);
                    $layout['data']["fieldValue"]["value"][$index]['source'] = $imageSource;
                    unset($layout['data']["fieldValue"]["value"][$index]['aurora_source']);
                }

            }
            $webBlock->updateQuietly([
                "layout" => $layout,
            ]);
        }

        $modelHasWebBlocksData = [
            'show_logged_in'     => $visibility['loggedIn'],
            'show_logged_out'    => $visibility['loggedOut'],
            "group_id"           => $webpage->group_id,
            "organisation_id"    => $webpage->organisation_id,
            "shop_id"            => $webpage->shop_id,
            "website_id"         => $webpage->website_id,
            "webpage_id"         => $webpage->id,
            "position"           => $position,
            "model_id"           => $webpage->id,
            "model_type"         => class_basename(Webpage::class),
            "web_block_id"       => $webBlock->id,
            "migration_checksum" => $migrationChecksum,
        ];

        if (isset($auroraBlock["show"])) {
            $modelHasWebBlocksData['show'] = boolval($auroraBlock["show"]);
        }


        $webpage->modelHasWebBlocks()->create($modelHasWebBlocksData);


        UpdateWebpageContent::run($webpage->refresh());

        BroadcastPreviewWebpage::dispatch($webpage);
    }

    private function processImage($webBlock, array|string $imageRawData, $webpage): array|null
    {
        if (!isset($imageRawData["aurora_source"])) {
            return null;
        }
        $auroraImage = $imageRawData["aurora_source"];

        $auroraImage = Str::startsWith($auroraImage, "/") ? $auroraImage : "/".$auroraImage;

        $media = FetchAuroraWebBlockMedia::run($webBlock, $webpage, $auroraImage);

        if ($media == null) {
            return null;
        }

        $image = $media->getImage();

        return GetPictureSources::run($image);
    }

    private function reset(Webpage $webpage): void
    {
        foreach ($webpage->webBlocks()->get() as $webBlock) {
            DeleteWebBlock::run($webBlock);
        }
    }

    /**
     * @throws \Exception
     */
    public function action(Webpage $webpage): Webpage
    {
        $this->initialisation($webpage->organisation, []);

        return $this->handle($webpage);
    }

    public function deleteWebBlockHaveOldChecksum(Webpage $webpage, array $oldChecksum): void
    {
        foreach ($webpage->webBlocks as $webBlock) {
            if (isset($oldChecksum[$webBlock->migration_checksum])) {
                DeleteWebBlock::run($webBlock);
            }
        }
    }

    public string $commandSignature = "fetch:web-blocks {webpage?} {--reset} {--d|db_suffix=}";

    /**
     * @throws \Exception
     */
    public function asCommand($command): int
    {
        if ($command->argument("webpage")) {
            try {
                /** @var Webpage $webpage */
                $webpage = Webpage::where("slug", $command->argument("webpage"))->firstOrFail();
            } catch (Exception) {
                $command->error("Webpage not found");
                exit();
            }
            $this->handle($webpage, $command->option("reset"), $command->option("db_suffix"));
            $command->line("Webpage ".$webpage->slug." web blocks fetched");

        } else {
            foreach (Webpage::orderBy('id')->get() as $webpage) {
                // if($webpage->slug != "storefront-uk") {
                // }
                $command->line("Webpage ".$webpage->slug." web blocks fetched");
                $this->handle($webpage, $command->option("reset"), $command->option("db_suffix"));
                // $command->line("Webpage ".$webpage->slug." web blocks fetched2");
            }
        }



        return 0;
    }
}
