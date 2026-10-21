<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\StoredItem;

use App\Enums\EnumHelperTrait;

enum StoredItemAttachmentScopeEnum: string
{
    use EnumHelperTrait;

    case MANUAL = 'Manual';

    public static function labels(): array
    {
        return [
            'Manual' => __('Manual'),
        ];
    }

    /**
     * @return array<int, array{name: string, code: string}>
     */
    public static function options(): array
    {
        return collect(self::labels())
            ->map(fn (string $label, string $code) => ['name' => $label, 'code' => $code])
            ->values()
            ->all();
    }
}
