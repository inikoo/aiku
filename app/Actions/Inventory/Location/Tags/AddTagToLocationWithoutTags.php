<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Dec 2023 22:19:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location\Tags;

use App\Actions\Helpers\Tag\Hydrators\TagHydrateProspects;
use App\Actions\Helpers\Tag\Hydrators\TagHydrateSubjects;
use App\Actions\Helpers\Tag\StoreTag;
use App\Actions\CRM\Prospect\Tags\Hydrators\TagHydrateUniversalSearch;
use App\Actions\Traits\WithImportModel;
use App\Models\CRM\Prospect;
use App\Models\Catalogue\Shop;
use Exception;

class AddTagToLocationWithoutTags
{
    use WithImportModel;

    public function handle(Shop $shop, $tagName): void
    {
        $tag = StoreTag::run([
            'name' => $tagName,
            'type' => 'crm'
        ]);


        $prospects = Prospect::where('shop_id', $shop->id)->get();
        $prospects->each(function ($prospect) use ($tag) {
            if ($prospect->tags()->count() == 0) {
                $prospect->attachTag($tag->name, 'crm');
            }
        });

        TagHydrateSubjects::dispatch($tag);
        TagHydrateProspects::dispatch($tag);
        TagHydrateUniversalSearch::dispatch($tag);
    }


    public string $commandSignature = 'prospects:add-tag-to-prospects-with-without-tags {shop} {tag}';

    public function asCommand($command): int
    {
        try {
            $shop = Shop::where('slug', $command->argument('shop'))->firstOrFail();
        } catch (Exception) {
            $command->error('Shop not found');
            exit;
        }

        $this->handle($shop, $command->argument('tag'));

        return 0;
    }


}
