<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-11h-15m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentFileScopeEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentFile;

class ProductDevelopmentDesignImages
{
    /**
     * @return array<int, array{id: int, url: string|null, name: string|null}>
     */
    public static function forDevelopment(ProductDevelopment $productDevelopment): array
    {
        return $productDevelopment->files()
            ->where('scope', ProductDevelopmentFileScopeEnum::DESIGN_IMAGE->value)
            ->with('media')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ProductDevelopmentFile $file) => self::row($file))
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, url: string|null, name: string|null}
     */
    public static function row(ProductDevelopmentFile $file): array
    {
        return [
            'id'   => $file->id,
            'url'  => $file->media ? route('grp.media.download', ['media' => $file->media->ulid, 'inline' => 1]) : null,
            'name' => $file->media?->file_name,
        ];
    }
}
