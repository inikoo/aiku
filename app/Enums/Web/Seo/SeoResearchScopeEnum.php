<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Enums\Web\Seo;

use App\Enums\EnumHelperTrait;
use Illuminate\Support\Str;

enum SeoResearchScopeEnum: string
{
    use EnumHelperTrait;

    case ROOT_DOMAIN = 'root_domain';
    case EXACT_URL   = 'exact_url';
    case SUBDOMAIN   = 'subdomain';
    case SUBFOLDER   = 'subfolder';

    public static function labels(): array
    {
        return [
            self::ROOT_DOMAIN->value => __('Root domain'),
            self::EXACT_URL->value   => __('Exact URL'),
            self::SUBDOMAIN->value   => __('Subdomain'),
            self::SUBFOLDER->value   => __('Subfolder'),
        ];
    }

    public static function guess(string $url): self
    {
        $url  = trim($url);
        $path = (string) parse_url(Str::contains($url, '://') ? $url : "https://$url", PHP_URL_PATH);

        if (trim($path, '/') === '') {
            return self::ROOT_DOMAIN;
        }

        return Str::endsWith($path, '/') ? self::SUBFOLDER : self::EXACT_URL;
    }
}
