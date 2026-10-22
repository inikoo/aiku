<?php

/*
 * Created: Thu, 22 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Web\ModelHasWebBlocks;

use App\Actions\Web\WebBlock\RemapWebBlockLayoutLinksToWebsite;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\User;
use App\Models\Web\WebBlock;
use App\Models\Web\Website;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;

trait WithPasteWebBlockFromSource
{
    private int $numberUnmatchedLinks = 0;

    private function layoutForWebsite(WebBlock $sourceWebBlock, Website $website): mixed
    {
        $remappedLayout             = RemapWebBlockLayoutLinksToWebsite::run($sourceWebBlock->layout, $website);
        $this->numberUnmatchedLinks = $remappedLayout['number_unmatched_links'];

        return $remappedLayout['layout'];
    }

    private function copyImages(WebBlock $sourceWebBlock, WebBlock $webBlock): void
    {
        $attachedMediaIds = DB::table('model_has_media')
            ->where('model_type', 'WebBlock')
            ->where('model_id', $webBlock->id)
            ->pluck('media_id')
            ->all();

        $rows = DB::table('model_has_media')
            ->where('model_type', 'WebBlock')
            ->where('model_id', $sourceWebBlock->id)
            ->whereNotIn('media_id', $attachedMediaIds)
            ->get()
            ->map(function (object $row) use ($webBlock) {
                $row = (array) $row;
                unset($row['id']);

                return array_merge($row, [
                    'model_id'        => $webBlock->id,
                    'source_id'       => null,
                    'fetched_at'      => null,
                    'last_fetched_at' => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            });

        if ($rows->isNotEmpty()) {
            DB::table('model_has_media')->insert($rows->all());
        }
    }

    private function canReadSourceShop(User $user, Shop $shop): bool
    {
        $permissions = [
            "websites-view.$shop->organisation_id",
            "web.$shop->id",
            "web.$shop->id.view",
            "web.$shop->id.edit",
            "supervisor-web.$shop->id",
            "group-webmaster.view",
        ];

        if ($shop->fulfilment) {
            $permissions[] = "fulfilment-shop.{$shop->fulfilment->id}";
            $permissions[] = "fulfilment-shop.{$shop->fulfilment->id}.view";
            $permissions[] = "fulfilment-shop.{$shop->fulfilment->id}.edit";
        }

        return $user->authTo($permissions);
    }

    private function flashUnmatchedLinks(ActionRequest $request): void
    {
        if ($this->numberUnmatchedLinks === 0) {
            return;
        }

        $request->session()->flash('notification', [
            'title'       => __('Check the links of the pasted block'),
            'description' => trans_choice(
                ':count link still points to the other store because this website has no matching page|:count links still point to the other store because this website has no matching page',
                $this->numberUnmatchedLinks,
                ['count' => $this->numberUnmatchedLinks]
            ),
            'status'      => 'warning',
        ]);
    }
}
