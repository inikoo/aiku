<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\CRM\SocialMedia;

use App\Enums\EnumHelperTrait;

enum SocialMediaPostTypeEnum: string
{
    use EnumHelperTrait;

    case REEL     = 'reel';
    case CAROUSEL = 'carousel';
    case IMAGE    = 'image';
    case VIDEO    = 'video';
    case STORY    = 'story';
    case LINK     = 'link';
    case TEXT     = 'text';
    case OTHER    = 'other';

    public static function labels(): array
    {
        return [
            'reel'     => __('Reel'),
            'carousel' => __('Carousel'),
            'image'    => __('Single image'),
            'video'    => __('Video'),
            'story'    => __('Story'),
            'link'     => __('Link'),
            'text'     => __('Text'),
            'other'    => __('Other'),
        ];
    }

    public static function fromExport(?string $postType, SocialMediaPlatformEnum $platform): SocialMediaPostTypeEnum
    {
        $postType = strtolower(trim((string) $postType));

        return match (true) {
            str_contains($postType, 'reel')                                        => self::REEL,
            str_contains($postType, 'carousel'), str_contains($postType, 'album') => self::CAROUSEL,
            str_contains($postType, 'stor')                                        => self::STORY,
            str_contains($postType, 'photo'), str_contains($postType, 'image')    => self::IMAGE,
            str_contains($postType, 'video'), $platform === SocialMediaPlatformEnum::TIKTOK => self::VIDEO,
            str_contains($postType, 'link')                                        => self::LINK,
            str_contains($postType, 'text'), str_contains($postType, 'status')    => self::TEXT,
            default                                                                => self::OTHER,
        };
    }
}
