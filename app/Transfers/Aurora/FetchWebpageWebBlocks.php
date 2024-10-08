<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 08 Oct 2024 16:11:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Actions\Helpers\Images\GetPictureSources;
use App\Actions\Helpers\Media\SaveModelImages;
use App\Actions\OrgAction;
use App\Actions\Web\WebBlock\StoreWebBlock;
use App\Actions\Web\Webpage\UpdateWebpageContent;
use App\Events\BroadcastPreviewWebpage;
use App\Models\Helpers\Media;
use App\Models\Web\WebBlockType;
use App\Models\Web\Webpage;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FetchWebpageWebBlocks extends OrgAction
{
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
        if ($auroraBlock["type"] == "text") {
            $webBlockType = WebBlockType::where("slug", "text")->first();
            $block = $webBlockType->toArray();
            data_set($block, "data.fieldValue.value", $auroraBlock["text_blocks"][0]["text"]);
        } elseif ($auroraBlock["type"] == "images") {
            $webBlockType = WebBlockType::where("slug", "gallery")->first();
            $block = $webBlockType->toArray();

            $imagesArray = [];
            foreach ($auroraBlock["images"] as $image) {
                if (!isset($image["src"])) {
                    continue;
                }
                $imagesArray[] = [
                    "aurora_source" => $image["src"],
                ];
            }
            $fieldValue["value"] = $imagesArray;
            data_set($block, "data.fieldValue.value", $fieldValue["value"]);
        } elseif ($auroraBlock["type"] == "iframe") {
            $webBlockType = WebBlockType::where("slug", "iframe")->first();
            $block = $webBlockType->toArray();
            data_set($block, "data.fieldValue.link", $auroraBlock["src"]);
        } elseif ($auroraBlock["type"] == "product") {
            $webBlockType = WebBlockType::where("slug", "product")->first();
            $block = $webBlockType->toArray();
            data_set($block, "data.fieldValue.value.text", $auroraBlock["text"]);
            data_set(
                $block,
                "data.fieldValue.value.image.aurora_source",
                $auroraBlock["image"]["src"]
            );

            $otherImages = [];
            foreach ($auroraBlock["other_images"] as $image) {
                if (!isset($image["src"])) {
                    continue;
                }
                $otherImages[] = [
                    "aurora_source" => $image["src"],
                ];
            }
            $imgValue["value"] = $otherImages;
            data_set($block, "data.fieldValue.value.other_images", $imgValue["value"]);
        } elseif ($auroraBlock["type"] == "blackboard") {
            $webBlockType = WebBlockType::where("slug", "overview")->first();
            $block = $webBlockType->toArray();
            $textsArray = [];
            foreach ($auroraBlock["texts"] as $text) {
                if (!isset($text["text"])) {
                    continue;
                }
                $textsArray[] = [
                    "text" => $text["text"],
                ];
            }
            $textValue["value"] = $textsArray;
            data_set($block, "data.fieldValue.value.texts", $textValue["value"]);

            $imagesArray = [];
            foreach ($auroraBlock["images"] as $image) {
                if (!isset($image["src"])) {
                    continue;
                }
                $imagesArray[] = [
                    "aurora_source" => $image["src"],
                ];
            }
            $imgValue["value"] = $imagesArray;
            data_set($block, "data.fieldValue.value.images", $imgValue["value"]);
        } else {
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

        // dd($webBlock->webBlockType->name);
        if (
            $webBlock->webBlockType->name == "Gallery" ||
            $webBlock->webBlockType->name == "Overview" ||
            $webBlock->webBlockType->name == "Product showcase A"
        ) {
            $imageSources = [];
            $imageRawDatas = [];
            $imageRawData = "";
            switch ($webBlock->webBlockType->name) {
                case "Overview":
                    $imageRawDatas = $webBlock->layout["data"]["fieldValue"]["value"]["images"];
                    break;
                case "Product":
                    $imageRawDatas =
                        $webBlock->layout["data"]["fieldValue"]["value"]["other_images"];
                    $imageRawData = $webBlock->layout["data"]["fieldValue"]["value"]["image"];
                    break;
                default:
                    $imageRawDatas = $webBlock->layout["data"]["fieldValue"]["value"];
                    break;
            }

            if (isset($imageRawData)) {
                $imageSource = $this->processImage($webBlock, $imageRawData, $webpage);
                $imageSources[] = ["image" => ["source" => $imageSource]];
            }

            foreach ($imageRawDatas as $imageRawData) {
                // if (!isset($imageRawData["aurora_source"])) {
                // 	break;
                // }
                // $auroraImage = $imageRawData["aurora_source"];

                // $urlToFile = "https://www." . $webpage->website->domain . $auroraImage;
                // $content = file_get_contents($urlToFile);
                // $tempPath = tempnam(sys_get_temp_dir(), "img_");

                // $headers = get_headers($urlToFile, 1);
                // $mimeType = $headers["Content-Type"];

                // if ($mimeType == "image/jpeg") {
                // 	$extension = ".jpg";
                // } elseif ($mimeType == "image/png") {
                // 	$extension = ".png";
                // } else {
                // 	$extension = ".jpg";
                // }

                // $tempFile = $tempPath . $extension;

                // file_put_contents($tempFile, $content);

                // $media = SaveModelImages::run($webBlock, [
                // 	"path" => $tempFile,
                // 	"originalName" => "aurora_image",
                // ]);

                // $image = $media->getImage();
                // $imageSource = GetPictureSources::run($image);

                // $imageSources[] = ["image" => ["source" => $imageSource]];
                $imageSource = $this->processImage($webBlock, $imageRawData, $webpage);
                $imageSources[] = ["image" => ["source" => $imageSource]];
            }
            dd($imageSources);

            if ($webBlock->webBlockType->name == "Overview") {
                data_set($block, "data.fieldValue.value.images", $imageSources);
            } else {
                data_set($block, "data.fieldValue.value", $imageSources);
            }
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

        $media = $this->getMediaFromWebpage($webBlock, $webpage, $auroraImage);

        $image = $media->getImage();
        $imageSource = GetPictureSources::run($image);
        return $imageSource;
    }

    public function getMediaFromWebpage($webBlock, $webpage, $auroraImage): Media
    {
        $urlToFile = "https://www." . $webpage->website->domain . $auroraImage;
        $content = file_get_contents($urlToFile);
        $tempPath = tempnam(sys_get_temp_dir(), "img_");

        $headers = get_headers($urlToFile, 1);
        $mimeType = $headers["Content-Type"];

        if ($mimeType == "image/jpeg") {
            $extension = ".jpg";
        } elseif ($mimeType == "image/png") {
            $extension = ".png";
        } else {
            $extension = ".jpg";
        }

        $tempFile = $tempPath . $extension;

        file_put_contents($tempFile, $content);

        $media = SaveModelImages::run($webBlock, [
            "path" => $tempFile,
            "originalName" => "aurora_image",
        ]);
        return $media;
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

    public function asCommand($command): int
    {
        try {
            $webpage = Webpage::where("slug", $command->argument("webpage"))->firstOrFail();
        } catch (Exception) {
            $command->error("Webpage not found");
            exit();
        }

        if ($command->option("reset")) {
            $this->reset($webpage);
        }

        $this->handle($webpage);

        return 0;
    }
}
