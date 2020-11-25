<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 23 Nov 2020 14:01:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\ECommerce\WebBlock;
use App\Models\ECommerce\Webpage;
use App\Models\ECommerce\Website;
use App\Models\Stores\Product;
use App\Models\Helpers\Category;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

function relocate_webpages($tenant, $legacy_data) {


    $legacy_content = json_decode($legacy_data->{'Page Store Content Data'}, true);
    //$legacy_properties = json_decode($legacy_data->{'Webpage Properties'}, true);

    unset($legacy_data->{'Page Store Content Data'});
    unset($legacy_data->{'Page Store Content Published Data'});
    unset($legacy_data->{'Webpage Navigation Data'});
    unset($legacy_data->{'Webpage Properties'});


    $webpage_data = fill_legacy_data(
        [
            'url' => 'Webpage URL'

        ], $legacy_data
    );

    $website = Website::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Webpage Website Key'});

    $webpageable_type = null;
    $webpageable_id   = null;
    if ($legacy_data->{'Webpage Scope'} == 'Product') {
        $webpageable      = (new Product)->firstWhere('legacy_id', $legacy_data->{'Webpage Scope Key'});
        $webpageable_type = 'Product';
        $webpageable_id   = $webpageable->id;

    } elseif ($legacy_data->{'Webpage Scope'} == 'Category Categories' or $legacy_data->{'Webpage Scope'} == 'Category Products') {
        $webpageable = Category::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Webpage Scope Key'});

        if ($webpageable) {
            $webpageable_type = 'Category';
            $webpageable_id   = $webpageable->id;
        } else {
            print_r($legacy_data);

        }

    }


    $webpage = (new Webpage())->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Page Key'},

        ], [
            'tenant_id'        => $tenant->id,
            'website_id'       => $website->id,
            'path'             => mb_strtolower($legacy_data->{'Webpage Code'}),
            'status'           => $legacy_data->{'Webpage State'} == 'Online',
            'webpageable_type' => $webpageable_type,
            'webpageable_id'   => $webpageable_id,
            'state'            => Str::snake($legacy_data->{'Webpage State'}),
            'data'             => $webpage_data,
            'created_at'       => $legacy_data->{'Webpage Creation Date'},
        ]
    );
    if (isset($legacy_content['blocks'])) {
        relocate_web_blocks($tenant, $webpage, $legacy_content['blocks']);
    }

    return $webpage;
}


function relocate_web_blocks($tenant, $webpage, $blocks) {

    $oldWebBlocks        = [];
    $webBlocksBridge     = [];
    $webBlocksPrecedence = [];

    foreach ($webpage->webBlocks as $webBlock) {
        $oldWebBlocks[$webBlock->id] = $webBlock->type;
    }


    foreach ($blocks as $block_key => $block) {
        $key = array_search($block['type'], $oldWebBlocks);
        if ($key !== false) {
            $webBlocksBridge[$block_key] = $key;
            unset($oldWebBlocks[$key]);
        } else {
            $webBlocksBridge[$block_key] = null;
        }
    }


    foreach ($blocks as $block_key => $block) {
        unset($block['label']);
        unset($block['icon']);

        $top_margin = Arr::pull($block, 'top_margin', 0);
        if ($top_margin > 0) {
            Arr::set($block, 'margin.top', $top_margin);
        }
        $bottom_margin = Arr::pull($block, 'bottom_margin', 0);
        if ($top_margin > 0) {
            Arr::set($block, 'margin.bottom', $bottom_margin);
        }
        $left_margin = Arr::pull($block, 'left_margin', 0);
        if ($top_margin > 0) {
            Arr::set($block, 'margin.left', $left_margin);
        }
        $right_margin = Arr::pull($block, 'right_margin', 0);
        if ($top_margin > 0) {
            Arr::set($block, 'margin.right', $right_margin);
        }


        $type = Arr::pull($block, 'type');
        $show = Arr::pull($block, 'show') == 1;


        if ($webBlocksBridge[$block_key] === null) {
            $webBlock              = $webpage->webBlocks()->create(
                [
                    'type'   => $type,
                    'status' => $show,
                    'data'   => $block
                ]
            );
            $webBlocksPrecedence[] = $webBlock->id;
        } else {
            $webBlock = (new webBlock)->find($webBlocksBridge[$block_key]);
            $webBlock->fill(
                [
                    'status' => $show,
                    'data'   => $block
                ]
            );
            $webBlock->save();
            $webBlocksPrecedence[] = $webBlock->id;
        }


        $imagesModelData = [];

        //$translate_images=[];

        if ($type == 'images') {
            foreach ($block['images'] as $image) {

                if (preg_match('/wi.php\?id=(\d+)/', $image['src'], $match)) {
                    $image_key = $match[1];

                    $legacy_image_data = get_legacy_image_data($tenant, $image_key);
                    if ($legacy_image_data) {

                        $imagesModelData[] = get_legacy_image_data($tenant, $image_key);
                        //todo get data to later translate ols block data to the new image id
                    }


                }


            }

        }

        if (count($imagesModelData)) {

            sync_images(
                $webBlock, $imagesModelData, function ($_scope) {
                switch ($_scope) {
                    default:
                        return 'web_image';
                }

            }
            );


        }


    }

    webBlock::destroy($oldWebBlocks);
    $precedence = 0;
    foreach ($webBlocksPrecedence as $webBlocksID) {
        $webBlock             = (new webBlock)->find($webBlocksID);
        $webBlock->precedence = $precedence;
        $webBlock->save();
        $precedence++;
    }


}
