<?php

namespace App\Http\Resources\Traits;

use App\Enums\Catalogue\Product\ProductBadgeEnum;
use Illuminate\Support\Carbon;

trait HasProductBadge
{
    /**
     * @return array{badge: string|null, first_available_at: string|null, back_in_stock_since: string|null, out_of_stock_since: string|null}
     */
    protected function getProductBadgeData(): array
    {
        $toIso = fn ($date): ?string => $date ? Carbon::parse($date)->toIso8601String() : null;

        return [
            'badge'               => ProductBadgeEnum::resolve($this->first_available_at, $this->top_seller)?->value,
            'first_available_at'  => $toIso($this->first_available_at),
            'back_in_stock_since' => $toIso($this->back_in_stock_since),
            'out_of_stock_since'  => $toIso($this->out_of_stock_since),
        ];
    }
}
