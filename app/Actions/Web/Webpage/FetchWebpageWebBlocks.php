<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 07 Oct 2024 17:36:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\Helpers\Images\GetImgProxyUrl;
use App\Actions\OrgAction;
use App\Actions\Web\WebBlock\StoreWebBlock;
use App\Events\BroadcastPreviewWebpage;
use App\Models\Web\WebBlockType;
use App\Models\Web\Webpage;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

            } elseif ($auroraBlock['type'] == 'images') {

                $webBlockType = WebBlockType::where("slug", "gallery")->first();
                $block        = $webBlockType->toArray();

                foreach ($auroraBlock['images'] as $image) {
                    $urlToFile = 'https://www.ancientwisdom.biz/'.$image['src'];

                    $headers = get_headers($urlToFile, 1);
                    $mimeType = $headers['Content-Type'];


                    if ($mimeType == 'image/jpeg') {
                        $extension = '.jpg';
                    } elseif ($mimeType == 'image/png') {
                        $extension = '.png';
                    } else {
                        $extension = '.jpg';
                    }

                    $safeFileName = uniqid() . $extension;

                    $media = group()->addMediaFromUrl($urlToFile)
                                    ->usingFileName($safeFileName)
                                    ->withProperties(
                                        [
                                                'group_id' => group()->id,
                                                'ulid'     => Str::ulid()
                                            ],
                                    )
                                    ->toMediaCollection('images');

                    $media->refresh();
                    $image = $media->getImage();
                    $imageUrl = GetImgProxyUrl::run($image);
                    $fieldValue['value'][] = [
                        'id' => $media->id,
                        'text' => "<h2><span style='font-size: 36px'>blabla</span></h2>",
                        'image' => [
                            'source' => [
                                'original' => $imageUrl
                            ]
                        ]
                    ];

                    data_set($block, "data.fieldValue.value", $fieldValue['value']);
                }
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
