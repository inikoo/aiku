<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 08 Oct 2024 16:16:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Web\WebBlock\DeleteWebBlock;
use App\Models\Catalogue\Product;
use Illuminate\Support\Str;
use App\Actions\Helpers\Images\GetPictureSources;
use App\Actions\OrgAction;
use App\Actions\Traits\WebBlocks\WithFetchCTA1WebBlock;
use App\Actions\Traits\WebBlocks\WithFetchGalleryWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchIFrameWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchOverviewWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchProductWebBlock;
use App\Actions\Traits\WebBlocks\WithFetchTextWebBlock;
use App\Actions\Traits\WithOrganisationSource;
use App\Actions\Web\WebBlock\StoreWebBlock;
use App\Actions\Web\Webpage\UpdateWebpageContent;
use App\Events\BroadcastPreviewWebpage;
use App\Models\Web\WebBlockType;
use App\Models\Web\Webpage;
use App\Transfers\AuroraOrganisationService;
use App\Transfers\WowsbarOrganisationService;
use Exception;
use Illuminate\Support\Arr;

class FetchWebBlocks extends OrgAction
{
    use WithAuroraOrganisationsArgument;
    use WithOrganisationSource;
    use WithFetchTextWebBlock;
    use WithFetchGalleryWebBlock;
    use WithFetchIFrameWebBlock;
    use WithFetchProductWebBlock;
    use WithFetchOverviewWebBlock;
    use WithFetchCTA1WebBlock;

    protected AuroraOrganisationService|WowsbarOrganisationService|null $organisationSource = null;

    public function handle(Webpage $webpage, $reset = false): Webpage
    {
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
                    "loggedIn" => true,
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
                    "loggedIn" => false,
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
                $layout = $this->processGalleryData($webBlockType, $auroraBlock);
                break;
            case "text":
                $webBlockType = WebBlockType::where("slug", "text")->first();
                $layout = $this->processTextData($webBlockType, $auroraBlock);
                break;
            case "iframe":
                $webBlockType = WebBlockType::where("slug", "iframe")->first();
                $layout = $this->processIFrameData($webBlockType, $auroraBlock);
                break;
            case "product":
                $webBlockType = WebBlockType::where("slug", "product")->first();
                $layout = $this->processProductData($webBlockType, $auroraBlock);
                $models[] = Product::find($webpage->model_id);

                break;
            case "blackboard":
                $webBlockType = WebBlockType::where("slug", "overview")->first();
                $layout = $this->processOverviewData($webBlockType, $auroraBlock);
                break;
            case "button":
                $webBlockType = WebBlockType::where("slug", "cta1")->first();
                $layout = $this->processCTA1Data($webBlockType, $auroraBlock);
                break;
            default:
                print ">>>>> " . $webpage->slug . "  " . $auroraBlock["type"] . "  <<<<<<\n";

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
                "layout" => $layout,
                "migration_checksum" => $migrationData,
                "visibility" => $visibility,
                "models" => $models,
            ],
            strict: false
        );

        if (
            $webBlock->webBlockType->code == "gallery" ||
            $webBlock->webBlockType->code == "overview" ||
            $webBlock->webBlockType->code == "cta1"
        ) {
            $imageSources = [];


            $imagesRawData = $webBlock->layout["data"]["fieldValue"]["value"]["images"];
            foreach ($imagesRawData as $imageRawData) {
                $imageSource = $this->processImage($webBlock, $imageRawData, $webpage);
                $imageSources[] = ["image" => ["source" => $imageSource]];
            }

            data_set($layout, "data.fieldValue.value.images", $imageSources);

            $webBlock->updateQuietly([
                "layout" => $layout,
            ]);
        }

        $webpage->modelHasWebBlocks()->create([
            "group_id" => $webpage->group_id,
            "organisation_id" => $webpage->organisation_id,
            "shop_id" => $webpage->shop_id,
            "website_id" => $webpage->website_id,
            "webpage_id" => $webpage->id,
            "position" => $position,
            "model_id" => $webpage->id,
            "model_type" => class_basename(Webpage::class),
            "web_block_id" => $webBlock->id,
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

        $auroraImage = Str::startsWith($auroraImage, "/") ? $auroraImage : "/" . $auroraImage;

        $media = FetchWebBlockMedia::run($webBlock, $webpage, $auroraImage);

        if ($media == null) {
            return null;
        }

        $image = $media->getImage();

        return GetPictureSources::run($image);
    }

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

    public string $commandSignature = "fetch:web-blocks {webpage} {--reset}";

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

        $this->organisationSource = $this->getOrganisationSource($webpage->organisation);
        $this->organisationSource->initialisation($webpage->organisation, "_base");

        $this->handle($webpage, $command->option("reset")); // hello

        return 0;
    }
}
