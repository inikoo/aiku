<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\SupplyChain\Supplier;

use App\Enums\EnumHelperTrait;

/**
 * Who packs a supplier's goods for us: the factory itself, the agent in its own warehouse, or another supplier
 * that makes the packaging.
 */
enum SupplierPackingMethodEnum: string
{
    use EnumHelperTrait;

    case SUPPLIER_PACKS = 'supplier_packs';
    case AGENT_PACKS = 'agent_packs';
    case THIRD_PARTY = 'third_party';

    public static function labels(): array
    {
        return [
            'supplier_packs' => __('Supplier packs'),
            'agent_packs'    => __('Agent packs in-house'),
            'third_party'    => __('Packaging supplier'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
