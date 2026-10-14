<?php

use App\Enums\Catalogue\Product\ProductBadgeEnum;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->now = Carbon::parse('2026-10-14 12:00:00');
});

it('shows NEW IN for 30 days from first availability', function () {
    expect(ProductBadgeEnum::resolve($this->now->copy()->subDays(29), null, $this->now))->toBe(ProductBadgeEnum::NEW_IN)
        ->and(ProductBadgeEnum::resolve($this->now->copy()->subDays(29)->toDateTimeString(), null, $this->now))->toBe(ProductBadgeEnum::NEW_IN)
        ->and(ProductBadgeEnum::resolve($this->now->copy()->subDays(30), null, $this->now))->toBeNull()
        ->and(ProductBadgeEnum::resolve(null, null, $this->now))->toBeNull();
});

it('ranks NEW IN over BESTSELLER and gives the bestseller back once the 30 days are over', function () {
    expect(ProductBadgeEnum::resolve($this->now->copy()->subDay(), 2, $this->now))->toBe(ProductBadgeEnum::NEW_IN)
        ->and(ProductBadgeEnum::resolve($this->now->copy()->subDays(31), 2, $this->now))->toBe(ProductBadgeEnum::BESTSELLER)
        ->and(ProductBadgeEnum::resolve(null, 3, $this->now))->toBe(ProductBadgeEnum::BESTSELLER);
});
