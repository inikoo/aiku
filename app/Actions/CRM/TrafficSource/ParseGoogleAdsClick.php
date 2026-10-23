<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Fri, 23 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource;

use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class ParseGoogleAdsClick
{
    use AsAction;

    public const string COOKIE = 'aiku_gac';

    public const int WINDOW_DAYS = 90;

    /**
     * In Google's order of preference: gbraid and wbraid only stand in for a gclid on iOS clicks.
     */
    public const array CLICK_ID_TYPES = ['gclid', 'gbraid', 'wbraid'];

    /**
     * Parses the `aiku_gac` cookie, shaped `<timestamp>|<click_id_type>|<click_id>`.
     *
     * @return array{click_id: string, click_id_type: string, clicked_at: Carbon}|null
     */
    public function handle(?string $data): ?array
    {
        $parts = explode('|', trim((string) $data));

        if (count($parts) !== 3) {
            return null;
        }

        [$timestamp, $clickIdType, $clickId] = $parts;

        if (!ctype_digit($timestamp) || !in_array($clickIdType, self::CLICK_ID_TYPES, true) || !self::isValidClickId($clickId)) {
            return null;
        }

        $clickedAt = Carbon::createFromTimestampUTC((int) $timestamp);

        if ($clickedAt->isFuture()) {
            return null;
        }

        return [
            'click_id'      => $clickId,
            'click_id_type' => $clickIdType,
            'clicked_at'    => $clickedAt,
        ];
    }

    /**
     * @return array{click_id: string, click_id_type: string}|null
     */
    public static function fromUrl(string $url): ?array
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $queryParams);

        foreach (self::CLICK_ID_TYPES as $clickIdType) {
            $clickId = $queryParams[$clickIdType] ?? null;

            if (is_string($clickId) && self::isValidClickId($clickId)) {
                return [
                    'click_id'      => $clickId,
                    'click_id_type' => $clickIdType,
                ];
            }
        }

        return null;
    }

    /**
     * @param array{click_id: string, click_id_type: string, clicked_at: Carbon} $click
     */
    public static function serialise(array $click): string
    {
        return $click['clicked_at']->getTimestamp().'|'.$click['click_id_type'].'|'.$click['click_id'];
    }

    public static function isWithinWindow(?Carbon $clickedAt, ?Carbon $at = null): bool
    {
        $at ??= now();

        return $clickedAt !== null && $clickedAt->lte($at) && $clickedAt->gte($at->copy()->subDays(self::WINDOW_DAYS));
    }

    private static function isValidClickId(string $clickId): bool
    {
        return preg_match('/^[A-Za-z0-9_\-.~]{8,255}$/', $clickId) === 1;
    }
}
