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

    public const float U_SHAPED_MILESTONE_SHARE = 0.4;

    public const float U_SHAPED_MIDDLE_SHARE = 0.2;

    public const float W_SHAPED_MILESTONE_SHARE = 0.3;

    public const float W_SHAPED_MIDDLE_SHARE = 0.1;

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
            AttributionModelEnum::U_SHAPED    => $this->positionBased($touches, $registeredAt, $placedAt, $windowDays, ['first', 'order'], self::U_SHAPED_MILESTONE_SHARE, self::U_SHAPED_MIDDLE_SHARE),
            AttributionModelEnum::W_SHAPED    => $this->positionBased($touches, $registeredAt, $placedAt, $windowDays, ['first', 'registration', 'order'], self::W_SHAPED_MILESTONE_SHARE, self::W_SHAPED_MIDDLE_SHARE),
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
     * @return array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}|null
     */
    public function registrationTouch(array $touches, int $registeredAt): ?array
    {
        $rangeStart        = $registeredAt - self::ACQUISITION_RANGE_DAYS * self::DAY;
        $registrationTouch = null;

        foreach ($touches as $touch) {
            if ($touch['timestamp'] > $registeredAt) {
                break;
            }

            if ($touch['timestamp'] >= $rangeStart) {
                $registrationTouch = $touch;
            }
        }

        return $registrationTouch;
    }

    /**
     * @param array<int, array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}> $touches
     *
     * @return array{first: array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}|null, registration: array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}|null, order: array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}|null}
     */
    public function milestones(array $touches, int $registeredAt, int $placedAt, int $windowDays): array
    {
        $acquisitionTouch  = $this->acquisitionTouch($touches, $registeredAt);
        $firstTouch        = $acquisitionTouch ?? ($touches[0] ?? null);
        $registrationTouch = $acquisitionTouch ? $this->registrationTouch($touches, $registeredAt) : null;
        $orderTouch        = null;

        foreach ($touches as $touch) {
            if ($touch['timestamp'] > $placedAt) {
                break;
            }

            $orderTouch = $touch;
        }

        return [
            'first'        => $this->claims($firstTouch, $placedAt, $acquisitionTouch ? self::NEW_CUSTOMER_WINDOW_DAYS : self::EXISTING_CUSTOMER_WINDOW_DAYS) ? $firstTouch : null,
            'registration' => $this->claims($registrationTouch, $placedAt, self::NEW_CUSTOMER_WINDOW_DAYS) ? $registrationTouch : null,
            'order'        => $this->claims($orderTouch, $placedAt, $windowDays) ? $orderTouch : null,
        ];
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

        if (!$this->claims($touch, $placedAt, $windowDays)) {
            return [];
        }

        return [$this->credit($touch, 1.0)];
    }

    /**
     * @param array<int, array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}> $touches
     * @param array<int, string> $roles
     *
     * @return array<int, array{type: string, campaign_ref: string|null, timestamp: int, share: float}>
     */
    private function positionBased(array $touches, int $registeredAt, int $placedAt, int $windowDays, array $roles, float $milestoneShare, float $middleShare): array
    {
        $milestones = array_filter(array_intersect_key($this->milestones($touches, $registeredAt, $placedAt, $windowDays), array_flip($roles)));

        if (empty($milestones)) {
            return [];
        }

        $credited = [];
        $weights  = [];

        foreach ($milestones as $touch) {
            $key            = $this->touchKey($touch);
            $credited[$key] = $touch;
            $weights[$key]  = ($weights[$key] ?? 0) + $milestoneShare;
        }

        $middleTouches = $this->middleTouches($touches, $milestones, $placedAt, $windowDays);

        foreach ($middleTouches as $touch) {
            $key            = $this->touchKey($touch);
            $credited[$key] = $touch;
            $weights[$key]  = $middleShare / count($middleTouches);
        }

        $total = array_sum($weights);

        uasort($credited, fn (array $a, array $b) => $a['timestamp'] <=> $b['timestamp']);

        return array_values(array_map(fn (string $key) => $this->credit($credited[$key], $weights[$key] / $total), array_keys($credited)));
    }

    /**
     * @param array<int, array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}> $touches
     * @param array<string, array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}> $milestones
     *
     * @return array<int, array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}>
     */
    private function middleTouches(array $touches, array $milestones, int $placedAt, int $windowDays): array
    {
        if (!isset($milestones['order'])) {
            return [];
        }

        $start         = min(array_map(fn (array $touch) => $touch['timestamp'], $milestones));
        $end           = $milestones['order']['timestamp'];
        $milestoneKeys = array_flip(array_map(fn (array $touch) => $this->touchKey($touch), array_values($milestones)));
        $lastOfPair    = [];

        foreach ($touches as $touch) {
            if ($touch['timestamp'] > $end) {
                break;
            }

            if ($touch['timestamp'] < $start || isset($milestoneKeys[$this->touchKey($touch)]) || !$this->claims($touch, $placedAt, $windowDays)) {
                continue;
            }

            $lastOfPair[$this->pairKey($touch)] = $touch;
        }

        return array_values($lastOfPair);
    }

    /**
     * @param array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}|null $touch
     */
    private function claims(?array $touch, int $placedAt, int $windowDays): bool
    {
        return $touch !== null
            && $touch['timestamp'] <= $placedAt
            && ($windowDays <= 0 || $placedAt <= $touch['timestamp'] + $windowDays * self::DAY);
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
     */
    public function touchKey(array $touch): string
    {
        return $touch['timestamp'].'|'.$touch['type']->value.'|'.$touch['campaign_ref'];
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
