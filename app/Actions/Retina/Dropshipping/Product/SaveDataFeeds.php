<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 08-05-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Dropshipping\Product;

use App\Actions\RetinaAction;
use App\Enums\Catalogue\Collection\CollectionStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Exports\Marketing\ProductsInCollectionExport;
use App\Exports\Marketing\ProductsInProductCategoryExport;
use App\Exports\Marketing\ProductsInShopExport;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class SaveDataFeeds extends RetinaAction
{
    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Exception
     */
    public function handle(Shop|ProductCategory|Collection $parent): void
    {
        $path          = static::path($parent);
        $temporaryPath = $path.'.'.Str::random(8).'.tmp';

        if ($parent instanceof ProductCategory) {
            Excel::store(new ProductsInProductCategoryExport($parent), $temporaryPath, 'data-feeds', \Maatwebsite\Excel\Excel::CSV);
        } elseif ($parent instanceof Collection) {
            Excel::store(new ProductsInCollectionExport($parent), $temporaryPath, 'data-feeds', \Maatwebsite\Excel\Excel::CSV);
        } else {
            Excel::store(new ProductsInShopExport($parent), $temporaryPath, 'data-feeds', \Maatwebsite\Excel\Excel::CSV);
        }

        Storage::disk('data-feeds')->move($temporaryPath, $path);
    }

    public static function path(Shop|ProductCategory|Collection $parent): string
    {
        $baseFilename = $parent instanceof ProductCategory ? $parent->type->value : Str::snake(class_basename($parent));

        return Str::snake(class_basename($parent)).'/'.$baseFilename.'_'.$parent->slug.'.csv';
    }

    public static function markAsRead(Shop|ProductCategory|Collection $parent): void
    {
        Cache::put(static::readCacheKey($parent), true, now()->addDays(7));
    }

    public static function readCacheKey(Shop|ProductCategory|Collection $parent): string
    {
        return 'data-feed-read:'.static::path($parent);
    }

    public function refreshIfRead(Shop|ProductCategory|Collection $parent): void
    {
        $this->refreshOrDelete($parent, Cache::has(static::readCacheKey($parent)));
    }

    public function refreshOrDelete(Shop|ProductCategory|Collection $parent, bool $isRead): void
    {
        if ($isRead) {
            $this->handle($parent);
        } else {
            Storage::disk('data-feeds')->delete(static::path($parent));
        }
    }


    public $commandSignature = 'data_feeds:save';

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function asCommand(Command $command): int
    {
        $shops = Shop::where('is_aiku', true)
            ->whereIn('state', [ShopStateEnum::OPEN, ShopStateEnum::CLOSING_DOWN])
            ->get();
        /** @var Shop $shop */
        foreach ($shops as $shop) {
            $productCategories = ProductCategory::where('shop_id', $shop->id)
                ->whereIn('state', [ProductCategoryStateEnum::ACTIVE, ProductCategoryStateEnum::DISCONTINUING])
                ->get(['id', 'slug', 'type']);
            $collections = Collection::where('shop_id', $shop->id)
                ->where('state', CollectionStateEnum::ACTIVE)
                ->get(['id', 'slug']);

            $parents   = [$shop, ...$productCategories->all(), ...$collections->all()];
            $readMarks = Cache::many(array_map(fn ($parent) => static::readCacheKey($parent), $parents));

            foreach ($parents as $parent) {
                $this->refreshOrDelete($parent, $readMarks[static::readCacheKey($parent)] !== null);
                $command->info(match (true) {
                    $parent instanceof Shop => 'Shop: '.$parent->name,
                    $parent instanceof ProductCategory => 'Product Category: '.$parent->slug,
                    default => 'Collection: '.$parent->slug,
                });
            }
        }


        return 0;
    }
}
