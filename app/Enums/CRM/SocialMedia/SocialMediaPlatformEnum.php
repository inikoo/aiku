<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\CRM\SocialMedia;

use App\Enums\EnumHelperTrait;

enum SocialMediaPlatformEnum: string
{
    use EnumHelperTrait;

    case FACEBOOK  = 'facebook';
    case INSTAGRAM = 'instagram';
    case TIKTOK    = 'tiktok';

    public static function labels(): array
    {
        return [
            'facebook'  => 'Facebook',
            'instagram' => 'Instagram',
            'tiktok'    => 'TikTok',
        ];
    }

    public static function fromText(?string $text): ?SocialMediaPlatformEnum
    {
        $text = strtolower((string) $text);

        return match (true) {
            str_contains($text, 'instagram'), str_starts_with($text, 'ig ') => self::INSTAGRAM,
            str_contains($text, 'tiktok')                                    => self::TIKTOK,
            str_contains($text, 'facebook'), str_contains($text, 'fb.')     => self::FACEBOOK,
            default                                                          => null,
        };
    }
}
