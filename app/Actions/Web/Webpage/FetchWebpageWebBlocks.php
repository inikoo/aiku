<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 07 Oct 2024 17:36:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\Helpers\Images\GetPictureSources;
use App\Actions\Helpers\Media\SaveModelImages;
use App\Actions\OrgAction;
use App\Actions\Web\WebBlock\StoreWebBlock;
use App\Events\BroadcastPreviewWebpage;
use App\Models\Web\WebBlockType;
use App\Models\Web\Webpage;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FetchWebpageWebBlocks extends OrgAction
{
    public function handle(Webpage $webpage): Webpage
    {

        foreach (Arr::get($webpage->migration_data, "blocks", []) as $auroraBlock) {
            $migrationData = md5(json_encode($auroraBlock));

            if ($auroraBlock['type'] == "text") {
                $webBlockType = WebBlockType::where("slug", "text")->first();
                $block        = $webBlockType->toArray();
                data_set($block, "data.fieldValue.value", $auroraBlock['text_blocks'][0]['text']);

            }

            if ($auroraBlock['type'] == 'images') {

                $webBlockType = WebBlockType::where("slug", "gallery")->first();
                $block = $webBlockType->toArray();


                foreach ($auroraBlock['images'] as $image) {
                    $imagesArray[] = [
                        'aurora_source' => $image['src']

                    ];
                }
                $fieldValue['value'] = $imagesArray;
                data_set($block, "data.fieldValue.value", $fieldValue['value']);
            }

            data_set($block, "data.properties.padding.unit", "px");
            data_set($block, "data.properties.padding.left.value", 20);
            data_set($block, "data.properties.padding.right.value", 20);
            data_set($block, "data.properties.padding.bottom.value", 20);
            data_set($block, "data.properties.padding.top.value", 20);
            $webBlock = StoreWebBlock::make()->action(
                $webBlockType,
                [
                    'layout'             => $block,
                    'migration_checksum' => $migrationData
                ],
                strict: false
            );

            if ($webBlock->webBlockType->name == 'Gallery') {
                $imageSources = [];
                foreach ($webBlock->layout['data']['fieldValue']['value'] as $imageRawData) {
                    if (!isset($imageRawData['aurora_source'])) {
                        break;
                    }
                    $auroraImage = $imageRawData['aurora_source'];

                    $urlToFile = 'https://www.'.$webpage->website->domain.$auroraImage;
                    $content = file_get_contents($urlToFile);
                    $tempPath = tempnam(sys_get_temp_dir(), 'img_');

                    $headers = get_headers($urlToFile, 1);
                    $mimeType = $headers['Content-Type'];

                    if ($mimeType == 'image/jpeg') {
                        $extension = '.jpg';
                    } elseif ($mimeType == 'image/png') {
                        $extension = '.png';
                    } else {
                        $extension = '.jpg';
                    }

                    $tempFile = $tempPath . $extension;

                    file_put_contents($tempFile, $content);

                    $media = SaveModelImages::run($webBlock, [
                        'path' => $tempFile,
                        'originalName' => 'aurora_image'
                    ]);

                    $image = $media->getImage();
                    $imageSource = GetPictureSources::run($image);
                    $imageSources[] = ["image" => ["source" => $imageSource]];
                }

                data_set($block, "data.fieldValue.value", $imageSources);
                $webBlock->update([
                    'layout'             => $block,
                ]);
            }

            $webpage->modelHasWebBlocks()->create(
                [
                    'group_id'           => $webpage->group_id,
                    'organisation_id'    => $webpage->organisation_id,
                    'shop_id'            => $webpage->shop_id,
                    'website_id'         => $webpage->website_id,
                    'webpage_id'         => $webpage->id,
                    'position'           => 1,
                    'model_id'           => $webpage->id,
                    'model_type'         => class_basename(Webpage::class),
                    'web_block_id'       => $webBlock->id,
                    'migration_checksum' => $migrationData
                ]
            );
            UpdateWebpageContent::run($webpage->refresh());

            BroadcastPreviewWebpage::dispatch($webpage);
        }


        return $webpage;
    }

    public function action(Webpage $webpage): Webpage
    {
        $this->initialisation($webpage->organisation, []);

        return $this->handle($webpage);
    }

    public function reset(Webpage $webpage)
    {
        $webBlocks = $webpage->webBlocks()->get();
        DB::table("model_has_web_blocks")->where("webpage_id", $webpage->id)->delete();

        foreach ($webBlocks as $block) {
            $block->forceDelete();
        }
    }

    public string $commandSignature = 'fetch:web-blocks {webpage} {--reset}';

    public function asCommand($command): int
    {
        try {
            $webpage = Webpage::where('slug', $command->argument('webpage'))->firstOrFail();

        } catch (Exception) {
            $command->error('Webpage not found');
            exit;
        }

        if ($command->option('reset')) {
            $this->reset($webpage);
        }

        $this->handle($webpage);

        return 0;
    }
}
