<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
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

class AddTagToLocation
{
    use WithImportModel;

    public function handle(Shop $shop, $tagName): void
    {
        $tag =StoreTag::run([
            'name' => $tagName,
            'type' => 'crm'
        ]);


        $prospects = Prospect::where('shop_id', $shop->id)->get();
        $prospects->each(function ($prospect) use ($tag) {
            $prospect->attachTag($tag->name, 'crm');
        });

        TagHydrateSubjects::run($tag);
        TagHydrateProspects::run($tag);
        TagHydrateUniversalSearch::dispatch($tag);
    }


    public string $commandSignature = 'prospects:add-tag {shop} {tag}';

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
