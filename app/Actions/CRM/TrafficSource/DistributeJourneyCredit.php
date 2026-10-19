<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Mon, 19 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource;

use App\Enums\CRM\TrafficSource\TrafficSourcesTypeEnum;
use App\Enums\Web\WebsiteVisitor\WebsiteVisitorChannelEnum;
use Lorisleiva\Actions\Concerns\AsAction;

class DistributeJourneyCredit
{
    use AsAction;

    public const string MODEL_W_SHAPED = 'w_shaped';
    public const string MODEL_U_SHAPED = 'u_shaped';
    public const string DIRECT         = 'direct';

    private const array W_SHAPED_ANCHORS = ['first' => 0.3, 'registration' => 0.3, 'last' => 0.3, 'middle' => 0.1];
    private const array U_SHAPED_ANCHORS = ['first' => 0.4, 'last' => 0.4, 'middle' => 0.2];

    /**
     * Splits one conversion across the channels of the visits that led to it. Direct and internal
     * visits are not channels, so they only receive credit when nothing else touched the customer.
     *
     * A new customer's journey is W-shaped: first touch, the touch that registered them and the last
     * touch before the conversion each get 30%, the touches between share 10%. Every other
     * conversion is U-shaped: 40% to the first and last touch, 20% shared by those between. Anchors
     * that land on the same touch add up, and when no touch sits between them the leftover share is
     * spread over the anchors, so the credit always totals 1.
     *
     * @param array<int, array{channel: string|null, at: int}> $touches oldest first
     *
     * @return array{model: string, credit: array<string, float>}
     */
    public function handle(array $touches, bool $isNewCustomer, ?int $registeredAt = null): array
    {
        $channelTouches = array_values(array_filter($touches, fn (array $touch) => $this->isChannel($touch['channel'])));

        if ($channelTouches === []) {
            return ['model' => self::MODEL_U_SHAPED, 'credit' => [self::DIRECT => 1.0]];
        }

        $registrationIndex = $isNewCustomer ? $this->registrationIndex($channelTouches, $registeredAt) : null;
        $model             = $registrationIndex === null ? self::MODEL_U_SHAPED : self::MODEL_W_SHAPED;

        $weights = $this->weights(
            count($channelTouches),
            $model === self::MODEL_W_SHAPED ? self::W_SHAPED_ANCHORS : self::U_SHAPED_ANCHORS,
            $registrationIndex
        );

        $credit = [];
        foreach ($channelTouches as $index => $touch) {
            $credit[$touch['channel']] = ($credit[$touch['channel']] ?? 0.0) + $weights[$index];
        }

        return ['model' => $model, 'credit' => $credit];
    }

    public function isChannel(?string $channel): bool
    {
        return $channel !== null
            && $channel !== TrafficSourcesTypeEnum::DIRECT->value
            && $channel !== WebsiteVisitorChannelEnum::INTERNAL_TYPE;
    }

    /**
     * @param array<int, array{channel: string|null, at: int}> $channelTouches
     */
    private function registrationIndex(array $channelTouches, ?int $registeredAt): ?int
    {
        if ($registeredAt === null) {
            return null;
        }

        $index = null;
        foreach ($channelTouches as $position => $touch) {
            if ($touch['at'] <= $registeredAt) {
                $index = $position;
            }
        }

        return $index;
    }

    /**
     * @param array<string, float> $shares
     *
     * @return array<int, float>
     */
    private function weights(int $touchCount, array $shares, ?int $registrationIndex): array
    {
        $anchors = [0 => $shares['first'], $touchCount - 1 => $shares['last']];

        if ($registrationIndex !== null) {
            $anchors[$registrationIndex] = ($anchors[$registrationIndex] ?? 0.0) + $shares['registration'];
        }

        $weights = array_fill(0, $touchCount, 0.0);
        foreach ($anchors as $index => $share) {
            $weights[$index] += $share;
        }

        $between = array_values(array_diff(array_keys($weights), array_keys($anchors)));
        foreach ($between as $index) {
            $weights[$index] = $shares['middle'] / count($between);
        }

        $total = array_sum($weights);

        return array_map(fn (float $weight) => $weight / $total, $weights);
    }
}
