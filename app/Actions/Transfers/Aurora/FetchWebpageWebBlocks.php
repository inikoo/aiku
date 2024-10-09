<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 08 Oct 2024 16:16:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use Illuminate\Support\Str;
use App\Actions\Helpers\Images\GetPictureSources;
use App\Actions\OrgAction;
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
use Illuminate\Support\Facades\DB;

class FetchWebpageWebBlocks extends OrgAction
{
    use WithAuroraOrganisationsArgument;
    use WithOrganisationSource;
    use WithFetchTextWebBlock;
    use WithFetchGalleryWebBlock;
    use WithFetchIFrameWebBlock;
    use WithFetchProductWebBlock;
    use WithFetchOverviewWebBlock;

    protected AuroraOrganisationService|WowsbarOrganisationService|null $organisationSource = null;

    public function handle(Webpage $webpage): Webpage
    {
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
        switch ($auroraBlock["type"]) {
            case "text":
                $webBlockType = WebBlockType::where("slug", "text")->first();
                $block = $this->processTextData($webBlockType, $auroraBlock);
                break;
            case "iframe":
                $webBlockType = WebBlockType::where("slug", "iframe")->first();
                $block = $this->processIFrameData($webBlockType, $auroraBlock);
                break;
            case "product":
                $webBlockType = WebBlockType::where("slug", "product")->first();
                $block = $this->processProductData($webBlockType, $auroraBlock);
                break;
            case "blackboard":
                $webBlockType = WebBlockType::where("slug", "overview")->first();
                $block = $this->processOverviewData($webBlockType, $auroraBlock);
                break;
            default:
                return;
        }

        data_set($block, "data.properties.padding.unit", "px");
        data_set($block, "data.properties.padding.left.value", 20);
        data_set($block, "data.properties.padding.right.value", 20);
        data_set($block, "data.properties.padding.bottom.value", 20);
        data_set($block, "data.properties.padding.top.value", 20);
        $webBlock = StoreWebBlock::make()->action(
            $webBlockType,
            [
                "layout" => $block,
                "migration_checksum" => $migrationData,
                "visibility" => $visibility,
            ],
            strict: false
        );

        if (
            $webBlock->webBlockType->name == "Gallery" ||
            $webBlock->webBlockType->name == "Overview" ||
            $webBlock->webBlockType->name == "Product showcase A"
        ) {
            $imageSources = [];
            $imageRawDatas = [];
            $imageSourceMain = [];
            switch ($webBlock->webBlockType->name) {
                case "Overview":
                    $imageRawDatas = $webBlock->layout["data"]["fieldValue"]["value"]["images"];
                    break;
                case "Product showcase A":
                    $imageRawDatas =
                        $webBlock->layout["data"]["fieldValue"]["value"]["other_images"];
                    $imageRawData = $webBlock->layout["data"]["fieldValue"]["value"]["image"];
                    $imageSource = $this->processImage($webBlock, $imageRawData, $webpage);
                    $imageSourceMain = ["source" => $imageSource];
                    break;
                default:
                    $imageRawDatas = $webBlock->layout["data"]["fieldValue"]["value"];
                    break;
            }

            foreach ($imageRawDatas as $imageRawData) {
                $imageSource = $this->processImage($webBlock, $imageRawData, $webpage);
                switch ($webBlock->webBlockType->name) {
                    case "Product showcase A":
                        $imageSources[] = ["source" => $imageSource];
                        break;
                    default:
                        $imageSources[] = ["image" => ["source" => $imageSource]];
                        break;
                }
            }

            switch ($webBlock->webBlockType->name) {
                case "Overview":
                    data_set($block, "data.fieldValue.value.images", $imageSources);
                    break;
                case "Product showcase A":
                    data_set($block, "data.fieldValue.value.image", $imageSourceMain);
                    data_set($block, "data.fieldValue.value.other_images", $imageSources);
                    break;
                default:
                    data_set($block, "data.fieldValue.value", $imageSources);
                    break;
            }
            unset($block["data"]["value"][$position - 1]["aurora_source"]);

            $webBlock->update([
                "layout" => $block,
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

    private function processImage($webBlock, $imageRawData, $webpage)
    {
        if (!isset($imageRawData["aurora_source"])) {
            return;
        }
        $auroraImage = $imageRawData["aurora_source"];

        $auroraImage = Str::startsWith($auroraImage, "/") ? $auroraImage : "/" . $auroraImage;

        $media = FetchWebBlockMedia::run($webBlock, $webpage, $auroraImage);
        $image = $media->getImage();

        $imageSource = GetPictureSources::run($image);

        return $imageSource;
    }

    public function action(Webpage $webpage): Webpage
    {
        $this->initialisation($webpage->organisation, []);

        return $this->handle($webpage);
    }

    public function reset(Webpage $webpage): void
    {
        $webBlocks = $webpage->webBlocks()->get();
        DB::table("model_has_web_blocks")
            ->where("webpage_id", $webpage->id)
            ->delete();

        foreach ($webBlocks as $block) {
            $block->forceDelete();
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

        if ($command->option("reset")) {
            $this->reset($webpage);
        }

        $this->organisationSource = $this->getOrganisationSource($webpage->organisation);
        $this->organisationSource->initialisation($webpage->organisation, "_base");

        $this->handle($webpage);

        return 0;
    }
}
