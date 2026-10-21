<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource\Attribution;

use App\Actions\CRM\TrafficSource\GetTrafficSourceFromRefererHeader;
use App\Enums\CRM\TrafficSource\AttributionModelEnum;
use App\Enums\CRM\TrafficSource\TrafficSourcesTypeEnum;
use Lorisleiva\Actions\Concerns\AsAction;

class SplitOrderAttribution
{
    use AsAction;

    public const int ACQUISITION_RANGE_DAYS = 90;

    public const int NEW_CUSTOMER_WINDOW_DAYS = 365;

    public const int EXISTING_CUSTOMER_WINDOW_DAYS = 90;

    private const int DAY = 86400;

    /**
     * @param array<int, array{timestamp: int|null, type: TrafficSourcesTypeEnum, campaign_ref: string|null}> $touches
     *
     * @return array<int, array{type: string, campaign_ref: string|null, timestamp: int, share: float}>
     */
    public function handle(array $touches, int $registeredAt, int $placedAt, AttributionModelEnum $model, int $windowDays): array
    {
        $touches = $this->usableTouches($touches);

        return match ($model) {
            AttributionModelEnum::LINEAR      => $this->linear($touches, $placedAt, $windowDays),
            AttributionModelEnum::FIRST_TOUCH => $this->firstTouch($touches, $registeredAt, $placedAt),
        };
    }

    /**
     * @param array<int, array{timestamp: int|null, type: TrafficSourcesTypeEnum, campaign_ref: string|null}> $touches
     *
     * @return array<int, array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}>
     */
    public function usableTouches(array $touches): array
    {
        $usable = array_values(array_filter(
            $touches,
            fn (array $touch) => $touch['timestamp'] !== null
                && $touch['type'] !== TrafficSourcesTypeEnum::DIRECT
                && (!in_array($touch['type'], TrafficSourcesTypeEnum::hostReferenced(), true)
                    || GetTrafficSourceFromRefererHeader::normaliseHost($touch['campaign_ref']) === $touch['campaign_ref'])
        ));

        usort($usable, fn (array $a, array $b) => $a['timestamp'] <=> $b['timestamp']);

        return $usable;
    }

    /**
     * @param array<int, array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}> $touches
     *
     * @return array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}|null
     */
    public function acquisitionTouch(array $touches, int $registeredAt): ?array
    {
        $rangeStart = $registeredAt - self::ACQUISITION_RANGE_DAYS * self::DAY;

        foreach ($touches as $touch) {
            if ($touch['timestamp'] >= $rangeStart && $touch['timestamp'] <= $registeredAt) {
                return $touch;
            }
        }

        return null;
    }

    /**
     * @param array<int, array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}> $touches
     *
     * @return array<int, array{type: string, campaign_ref: string|null, timestamp: int, share: float}>
     */
    private function linear(array $touches, int $placedAt, int $windowDays): array
    {
        $lastTouchOfPair = [];

        foreach ($touches as $touch) {
            if ($touch['timestamp'] > $placedAt) {
                break;
            }

            $lastTouchOfPair[$this->pairKey($touch)] = $touch;
        }

        $qualifying = array_values(array_filter(
            $lastTouchOfPair,
            fn (array $touch) => $windowDays <= 0 || $placedAt <= $touch['timestamp'] + $windowDays * self::DAY
        ));

        if (empty($qualifying)) {
            return [];
        }

        $share = 1 / count($qualifying);

        return array_map(fn (array $touch) => $this->credit($touch, $share), $qualifying);
    }

    /**
     * @param array<int, array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}> $touches
     *
     * @return array<int, array{type: string, campaign_ref: string|null, timestamp: int, share: float}>
     */
    private function firstTouch(array $touches, int $registeredAt, int $placedAt): array
    {
        $acquisitionTouch = $this->acquisitionTouch($touches, $registeredAt);
        $touch            = $acquisitionTouch ?? ($touches[0] ?? null);
        $windowDays       = $acquisitionTouch ? self::NEW_CUSTOMER_WINDOW_DAYS : self::EXISTING_CUSTOMER_WINDOW_DAYS;

        if (!$touch || $touch['timestamp'] > $placedAt || $placedAt > $touch['timestamp'] + $windowDays * self::DAY) {
            return [];
        }

        return [$this->credit($touch, 1.0)];
    }

    /**
     * @param array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null} $touch
     */
    private function pairKey(array $touch): string
    {
        return $touch['type']->value.'|'.($touch['campaign_ref'] ?? '');
    }

    /**
     * @param array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null} $touch
     *
     * @return array{type: string, campaign_ref: string|null, timestamp: int, share: float}
     */
    private function credit(array $touch, float $share): array
    {
        return [
            'type'         => $touch['type']->value,
            'campaign_ref' => $touch['campaign_ref'],
            'timestamp'    => $touch['timestamp'],
            'share'        => $share,
        ];
    }
}
