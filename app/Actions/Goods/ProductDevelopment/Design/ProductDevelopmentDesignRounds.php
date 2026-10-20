<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-10h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundStatusEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentDesignRound;

/**
 * Concept, packaging and label run one after the other: a round opens once the previous one has an
 * approved version, and a new version can only start when the round has none yet or was sent back.
 */
class ProductDevelopmentDesignRounds
{
    public const string CHECK_MATCHES_BRIEF    = 'matches_brief';
    public const string CHECK_COMPLIANCE_SPACE = 'compliance_space';
    public const string CHECK_PRINT_LIMITS     = 'print_limits';

    /**
     * @return array<string, array{label: string, by: string}>
     */
    public static function checks(): array
    {
        return [
            self::CHECK_MATCHES_BRIEF    => ['label' => __('Matches the brief'), 'by' => 'approver'],
            self::CHECK_COMPLIANCE_SPACE => ['label' => __('Space reserved for compliance text'), 'by' => 'approver'],
            self::CHECK_PRINT_LIMITS     => ['label' => __('Within factory print limits (agent confirmed)'), 'by' => 'agent'],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<ProductDevelopmentDesignRound>|\Illuminate\Database\Eloquent\Relations\HasMany<ProductDevelopmentDesignRound>
     */
    public static function scope(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRoundEnum $round, ?int $itemId = null)
    {
        return $productDevelopment->designRounds()
            ->where('round', $round->value)
            ->when(
                $round->isPerSku(),
                fn ($query) => $query->where('product_development_item_id', $itemId),
                fn ($query) => $query->whereNull('product_development_item_id')
            );
    }

    public static function latest(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRoundEnum $round, ?int $itemId = null): ?ProductDevelopmentDesignRound
    {
        return self::scope($productDevelopment, $round, $itemId)->orderByDesc('version')->first();
    }

    /**
     * A per-SKU round asked without a SKU is approved only once every SKU of the development is.
     */
    public static function isApproved(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRoundEnum $round, ?int $itemId = null): bool
    {
        if ($round->isPerSku() && $itemId === null) {
            $itemIds = $productDevelopment->items()->pluck('id');
            if ($itemIds->isEmpty()) {
                return false;
            }
            $approved = $productDevelopment->designRounds()
                ->where('round', $round->value)
                ->where('status', ProductDevelopmentDesignRoundStatusEnum::APPROVED->value)
                ->whereIn('product_development_item_id', $itemIds)
                ->distinct()
                ->count('product_development_item_id');

            return $approved === $itemIds->count();
        }

        return self::scope($productDevelopment, $round, $itemId)->where('status', ProductDevelopmentDesignRoundStatusEnum::APPROVED->value)->exists();
    }

    /**
     * @return array{done: int, total: int}
     */
    public static function skuProgress(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRoundEnum $round): array
    {
        $itemIds = $productDevelopment->items()->pluck('id');

        return [
            'done'  => $productDevelopment->designRounds()
                ->where('round', $round->value)
                ->where('status', ProductDevelopmentDesignRoundStatusEnum::APPROVED->value)
                ->whereIn('product_development_item_id', $itemIds)
                ->distinct()
                ->count('product_development_item_id'),
            'total' => $itemIds->count(),
        ];
    }

    public static function hasAnyVersion(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRoundEnum $round): bool
    {
        return $productDevelopment->designRounds()->where('round', $round->value)->exists();
    }

    /**
     * Packaging and label can be skipped when a development does not need them; concept never can.
     */
    public static function isSkipped(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRoundEnum $round): bool
    {
        return !empty($productDevelopment->data['skipped_rounds'][$round->value] ?? null);
    }

    /**
     * Done for the rest of the development: approved, or skipped.
     */
    public static function isSettled(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRoundEnum $round): bool
    {
        return self::isSkipped($productDevelopment, $round) || self::isApproved($productDevelopment, $round);
    }

    public static function isUnlocked(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRoundEnum $round): bool
    {
        $previous = $round->previous();

        return !$previous || self::isSettled($productDevelopment, $previous);
    }

    public static function isArtworkLocked(ProductDevelopment $productDevelopment): bool
    {
        return !empty($productDevelopment->data['artwork_locked_at'] ?? null);
    }

    public static function canStartVersion(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRoundEnum $round, ?int $itemId = null): bool
    {
        if (self::isArtworkLocked($productDevelopment) || self::isSkipped($productDevelopment, $round) || !self::isUnlocked($productDevelopment, $round) || ($round->isPerSku() && !$itemId)) {
            return false;
        }

        $latest = self::latest($productDevelopment, $round, $itemId);

        return !$latest || $latest->status === ProductDevelopmentDesignRoundStatusEnum::CHANGES_REQUESTED;
    }

    public static function allChecksTicked(ProductDevelopmentDesignRound $designRound): bool
    {
        return collect(array_keys(self::checks()))->every(fn (string $key) => !empty($designRound->data['checks'][$key] ?? null));
    }
}
