<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\OrgAgent;

use App\Enums\EnumHelperTrait;

enum OrgAgentOrderContainerEnum: string
{
    use EnumHelperTrait;

    case TWENTY_FOOT = '20ft';
    case FORTY_FOOT = '40ft';
    case FORTY_FOOT_HIGH_CUBE = '40ft_hc';

    public static function labels(): array
    {
        return [
            '20ft'    => __('20 ft container'),
            '40ft'    => __('40 ft container'),
            '40ft_hc' => __('40 ft high cube container'),
        ];
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public static function selectOptions(): array
    {
        return collect(self::labels())->map(fn (string $label, string $value) => [
            'label' => $label.' ('.self::from($value)->cbm().' m³)',
            'value' => $value,
        ])->values()->all();
    }

    /**
     * Internal volume in cubic metres. A loaded container never reaches it, so an order "worth 0.9
     * of a container" is already a full one in practice.
     */
    public function cbm(): float
    {
        return match ($this) {
            self::TWENTY_FOOT          => 33.2,
            self::FORTY_FOOT           => 67.7,
            self::FORTY_FOOT_HIGH_CUBE => 76.3,
        };
    }
}
