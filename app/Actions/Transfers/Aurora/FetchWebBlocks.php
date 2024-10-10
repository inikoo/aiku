<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 08 Oct 2024 16:16:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores.
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
use Illuminate\Support\Arr;

class FetchWebBlocks extends OrgAction
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

        if (isset($webpage->migration_data["both"])) {
            foreach (
                Arr::get($webpage->migration_data["both"], "blocks", []) as $index => $auroraBlock
            ) {
                $migrationData = md5(json_encode($auroraBlock));
                $this->processData($webpage, $auroraBlock, $migrationData, $index + 1);
            }
        }
        if (isset($webpage->migration_data["loggedIn"])) {
            foreach (
                Arr::get($webpage->migration_data["loggedIn"], "blocks", []) as $index => $auroraBlock
            ) {
                $migrationData = md5(json_encode($auroraBlock));
                $this->processData($webpage, $auroraBlock, $migrationData, $index + 1, [
                    "loggedIn"  => true,
                    "loggedOut" => false,
                ]);
            }
        }
        if (isset($webpage->migration_data["loggedOut"])) {
            foreach (
                Arr::get($webpage->migration_data["loggedOut"], "blocks", []) as $index => $auroraBlock
            ) {
                $migrationData = md5(json_encode($auroraBlock));
                $this->processData($webpage, $auroraBlock, $migrationData, $index + 1, [
                    "loggedIn"  => false,
                    "loggedOut" => true,
                ]);
            }
        }

        return $webpage;
    }

    private function processData(
        Webpage $webpage,
        $auroraBlock,
        $migrationData,
        int $position,
        $visibility = ["loggedIn" => true, "loggedOut" => true]
    ): void {
        $models = [];

        switch ($auroraBlock["type"]) {
            case "images":
                $webBlockType = WebBlockType::where("slug", "gallery")->first();
                $layout       = $this->processGalleryData($webBlockType, $auroraBlock);
                break;
            case "text":
                $webBlockType = WebBlockType::where("slug", "text")->first();
                $layout       = $this->processTextData($webBlockType, $auroraBlock);
                break;
            case "telephone":
                $webBlockType = WebBlockType::where("slug", "text")->first();
                $layout       = $this->processPhoneData($webBlockType, $auroraBlock);
                break;
            case "code":
            case "reviews":
                $webBlockType = WebBlockType::where("slug", "script")->first();
                $layout       = $this->processScriptData($webBlockType, $auroraBlock);
                break;
            case "map":
            case "iframe":
                $webBlockType = WebBlockType::where("slug", "iframe")->first();
                $layout       = $this->processIFrameData($webBlockType, $auroraBlock);
                break;
            case "product":
                $webBlockType = WebBlockType::where("slug", "product")->first();
                $layout       = $this->processProductData($webBlockType, $auroraBlock);
                $models[]     = Product::find($webpage->model_id);
                break;

            case "category_products":
                $webBlockType = WebBlockType::where("slug", "family")->first();
                $models[]     = ProductCategory::find($webpage->model_id);
                $layout       = $this->processFamilyData($webBlockType, $auroraBlock);
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
                $layout = $this->processProductsData($webBlockType, $auroraBlock);
                break;

            case "category_categories":
                $categoriesId = [];
                foreach ($auroraBlock["sections"] as $section) {
                    if (isset($section['items']) && count($section['items']) > 0) {
                        foreach ($section['items'] as $item) {
                            if ($item['type'] == 'category') {
                                $categoriesId[] = $item['category_key'];
                            }
                        }
                    }
                }
                break;

            case "blackboard":
                $webBlockType = WebBlockType::where("slug", "overview")->first();
                $layout       = $this->processOverviewData($webBlockType, $auroraBlock);
                break;
            case "button":
                $webBlockType = WebBlockType::where("slug", "cta1")->first();
                $layout       = $this->processCTA1Data($webBlockType, $auroraBlock);
                break;
            default:
                print ">>>>> ".$webpage->slug."  ".$auroraBlock["type"]."  <<<<<<\n";

                return;
        }

        if ($layout == null) {
            return;
        }

        data_set($layout, "data.properties.padding.unit", "px");
        data_set($layout, "data.properties.padding.left.value", 20);
        data_set($layout, "data.properties.padding.right.value", 20);
        data_set($layout, "data.properties.padding.bottom.value", 20);
        data_set($layout, "data.properties.padding.top.value", 20);
        $webBlock = StoreWebBlock::make()->action(
            $webBlockType,
            [
                "layout"             => $layout,
                "migration_checksum" => $migrationData,
                "visibility"         => $visibility,
                "models"             => $models,
            ],
            strict: false
        );

        if (
            $webBlock->webBlockType->code == "gallery"
            || $webBlock->webBlockType->code == "overview"
            || $webBlock->webBlockType->code == "cta3"
        ) {
            $imageSources = [];


            $imagesRawData = $webBlock->layout["data"]["fieldValue"]["value"]["images"];
            foreach ($imagesRawData as $imageRawData) {
                $imageSource    = $this->processImage($webBlock, $imageRawData, $webpage);
                $imageSources[] = ["image" => ["source" => $imageSource]];
            }

            data_set($layout, "data.fieldValue.value.images", $imageSources);

            $webBlock->updateQuietly([
                "layout" => $layout,
            ]);
        }

        $webpage->modelHasWebBlocks()->create([
            "group_id"           => $webpage->group_id,
            "organisation_id"    => $webpage->organisation_id,
            "shop_id"            => $webpage->shop_id,
            "website_id"         => $webpage->website_id,
            "webpage_id"         => $webpage->id,
            "position"           => $position,
            "model_id"           => $webpage->id,
            "model_type"         => class_basename(Webpage::class),
            "web_block_id"       => $webBlock->id,
            "migration_checksum" => $migrationData,
        ]);
        UpdateWebpageContent::run($webpage->refresh());

        BroadcastPreviewWebpage::dispatch($webpage);
    }

    private function processImage($webBlock, array $imageRawData, $webpage): array|null
    {
        if (!isset($imageRawData["aurora_source"])) {
            return null;
        }
        $auroraImage = $imageRawData["aurora_source"];

        $auroraImage = Str::startsWith($auroraImage, "/") ? $auroraImage : "/".$auroraImage;

        $media = FetchWebBlockMedia::run($webBlock, $webpage, $auroraImage);

        if ($media == null) {
            return null;
        }

        $image = $media->getImage();

        return GetPictureSources::run($image);
    }

    /**
     * @throws \Exception
     */
    public function action(Webpage $webpage): Webpage
    {
        $this->initialisation($webpage->organisation, []);

        return $this->handle($webpage);
    }

    public function reset(Webpage $webpage): void
    {
        foreach ($webpage->webBlocks()->get() as $webBlock) {
            DeleteWebBlock::run($webBlock);
        }
    }

    public string $commandSignature = "fetch:web-blocks {webpage} {--reset}  {--d|db_suffix=}";

    /**
     * @throws \Exception
     */
    public function asCommand($command): int
    {
        try {
            /** @var Webpage $webpage */
            $webpage = Webpage::where("slug", $command->argument("webpage"))->firstOrFail();
        } catch (Exception) {
            $command->error("Webpage not found");
            exit();
        }


        $this->handle($webpage, $command->option("reset"), $command->option("db_suffix"));

        return 0;
    }
}
