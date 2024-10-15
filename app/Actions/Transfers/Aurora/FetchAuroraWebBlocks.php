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
use App\Actions\Traits\WebBlocks\WithFetchGalleryWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchIFrameWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchOverviewWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchProductsWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchProductWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchScriptWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchSeeAlsoWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchTextWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchDepartmentWebBlock;
use App\Actions\Traits\WithOrganisationSource;
use App\Actions\Web\WebBlock\StoreWebBlock;
use App\Actions\Web\Webpage\UpdateWebpageContent;
use App\Events\BroadcastPreviewWebpage;
use App\Models\Catalogue\ProductCategory;
use App\Models\Web\WebBlockType;
use App\Models\Web\Webpage;
use App\Transfers\AuroraOrganisationService;
use App\Transfers\WowsbarOrganisationService;
use Exception;
use Illuminate\Support\Facades\DB;

class FetchAuroraWebBlocks extends OrgAction
{
    use WithAuroraParsers;
    use WithAuroraOrganisationsArgument;
    use WithOrganisationSource;
    use WithFetchTextWebBlock;
    use WithFetchGalleryWebBlock;
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

            foreach ($migrationTypes as $type) {
                if (isset($webpage->migration_data[$type])) {
                    $this->processMigrationData($webpage, $webpage->migration_data[$type]['blocks'], $oldMigrationsChecksum, $type);
                }
            }
        }

        if (count($oldMigrationsChecksum) > 0) {
            $this->deleteWebBlockHaveOldChecksum($webpage, $oldMigrationsChecksum);
        }

        return $webpage;
    }

    private function processMigrationData($webpage, array $blocks, array &$oldMigrationsChecksum, $type): void
    {
        foreach ($blocks as $index => $auroraBlock) {
            $migrationData = md5(json_encode($auroraBlock));
            if (isset($oldMigrationsChecksum[$migrationData])) {
                DB::table("model_has_web_blocks")->where('migration_checksum', $migrationData)->update(['position' => $index + 1]);
                unset($oldMigrationsChecksum[$migrationData]);
                continue;
            }

            $loggedInStatus = $type === 'loggedIn';
            $this->processData($webpage, $auroraBlock, $migrationData, $index + 1, [
                'loggedIn' => $loggedInStatus,
                'loggedOut' => !$loggedInStatus,
            ]);
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

        switch ($auroraBlock["type"]) {
            case "images":
                $webBlockType = WebBlockType::where("slug", "gallery")->first();
                $layout       = $this->processGalleryData($auroraBlock);
                break;
            case "text":
                $webBlockType = WebBlockType::where("slug", "text")->first();
                $layout       = $this->processTextData($auroraBlock);
                break;
            case "telephone":
                $webBlockType = WebBlockType::where("slug", "text")->first();
                $layout       = $this->processPhoneData($auroraBlock);
                break;
            case "code":
            case "reviews":
                $webBlockType = WebBlockType::where("slug", "script")->first();
                $layout       = $this->processScriptData($auroraBlock);
                break;
            case "map":
            case "iframe":
                $webBlockType = WebBlockType::where("slug", "iframe")->first();
                $layout       = $this->processIFrameData($auroraBlock);
                break;
            case "product":
                $webBlockType = WebBlockType::where("slug", "product")->first();
                $layout       = $this->processProductData($auroraBlock);
                $models[]     = Product::find($webpage->model_id);
                break;

            case "category_products":
                $webBlockType = WebBlockType::where("slug", "family")->first();
                $models[]     = ProductCategory::find($webpage->model_id);
                $layout       = $this->processFamilyData($auroraBlock);
                break;

            case "see_also":
                $webBlockType = WebBlockType::where("slug", "see_also")->first();
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
                $webBlockType = WebBlockType::where("slug", "products")->first();
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
                $webBlockType = WebBlockType::where("slug", "department")->first();
                $layout       = $this->processDepartmentData($models, $webpage, $auroraBlock);
                break;

            case "blackboard":
                $webBlockType = WebBlockType::where("slug", "overview")->first();
                $layout       = $this->processOverviewData($auroraBlock);
                break;
            case "button":
                $webBlockType = WebBlockType::where("slug", "cta1")->first();
                $layout       = $this->processCTA1Data($auroraBlock);
                break;
            default:
                print ">>>>> ".$webpage->slug."  ".$auroraBlock["type"]."  <<<<<<\n";

                return;
        }

        if ($layout == null) {
            return;
        }

        data_set($layout, "properties.padding.unit", "px");
        data_set($layout, "properties.padding.left.value", 20);
        data_set($layout, "properties.padding.right.value", 20);
        data_set($layout, "properties.padding.bottom.value", 20);
        data_set($layout, "properties.padding.top.value", 20);

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
            $webBlock->webBlockType->code == "gallery"
            || $webBlock->webBlockType->code == "overview"
            || $webBlock->webBlockType->code == "cta1"
            || $webBlock->webBlockType->code == "family"
            || $webBlock->webBlockType->code == "department"
        ) {
            $code = $webBlock->webBlockType->code;

            if ($code == "family") {
                $items  = $webBlock->layout["fieldValue"]["value"]["items"];
                $addOns = [];
                foreach ($items as $item) {
                    if ($item['type'] == "image") {
                        $imageSource = $this->processImage($webBlock, $item, $webpage);
                        $addOns[]    = ['position' => $item['position'], "type" => $item['type'], "source" => $imageSource];
                    } else {
                        $addOns[] = $item;
                    }
                }
                data_set($layout, "fieldValue.value.addOns", $addOns);
                unset($layout["fieldValue"]["value"]["items"]);
            } elseif ($code == "department") {
                $sections = $webBlock->layout["fieldValue"]["value"]["sections"];
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
                data_set($layout, "fieldValue.value.sections", $sections);
            } else {
                $imageSources  = [];
                $imagesRawData = $webBlock->layout["fieldValue"]["value"]["images"];
                foreach ($imagesRawData as $imageRawData) {
                    $imageSource    = $this->processImage($webBlock, $imageRawData, $webpage);
                    $imageSources[] = ["image" => ["source" => $imageSource]];
                }
                data_set($layout, "fieldValue.value.images", $imageSources);
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
                $this->handle($webpage, $command->option("reset"), $command->option("db_suffix"));
                $command->line("Webpage ".$webpage->slug." web blocks fetched");
            }
        }



        return 0;
    }
}
