<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\TradeUnit;

use App\Actions\Helpers\Translations\Translate;
use App\Events\TradeUnitNameTranslationProgressEvent;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Language;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * HELP-3997: the English name is translated into every shop language. Each translation keeps in
 * data.name_i8n_status the English name it was made from and whether a person or the translator
 * wrote it, so a new English name retranslates only the machine ones. A person's translation is
 * shown as outdated and retranslated only when someone asks for it ($includeOutdatedManual).
 */
class TranslateTradeUnitName implements ShouldBeUnique
{
    use AsAction;

    public string $jobQueue = 'translate-model';

    public function getJobUniqueId(TradeUnit $tradeUnit, bool $includeOutdatedManual = false): string
    {
        return $tradeUnit->id.($includeOutdatedManual ? '-outdated' : '');
    }

    /**
     * The edit form and the label window show the translation running from this flag, set when it
     * is queued, so a page opened or reloaded meanwhile still shows it.
     */
    public static function runningKey(TradeUnit $tradeUnit): string
    {
        return 'trade-unit-name-translation:'.$tradeUnit->id;
    }

    public static function isRunning(TradeUnit $tradeUnit): bool
    {
        return Cache::has(self::runningKey($tradeUnit));
    }

    public static function queue(TradeUnit $tradeUnit, bool $includeOutdatedManual = false): void
    {
        Cache::put(self::runningKey($tradeUnit), true, now()->addMinutes(15));
        static::dispatch($tradeUnit, $includeOutdatedManual);
    }

    /**
     * @return array<int, string> the languages the translator could not translate
     */
    public function handle(TradeUnit $tradeUnit, bool $includeOutdatedManual = false): array
    {
        try {
            return $this->translate($tradeUnit, $includeOutdatedManual);
        } finally {
            Cache::forget(self::runningKey($tradeUnit));
        }
    }

    /**
     * @return array<int, string>
     */
    private function translate(TradeUnit $tradeUnit, bool $includeOutdatedManual): array
    {
        $englishName = trim((string) $tradeUnit->name);
        $languages   = GetTradeUnitNameTranslationLanguages::run($tradeUnit->group);
        $names       = $tradeUnit->getTranslations('name_i8n');
        $statuses    = (array) data_get($tradeUnit->data, 'name_i8n_status', []);
        $failed      = [];

        $toTranslate = $englishName === '' ? collect() : Language::whereIn('code', $languages)->orderBy('code')->get()
            ->filter(fn (Language $language) => $this->needsTranslation($names[$language->code] ?? null, $statuses[$language->code] ?? null, $englishName, $includeOutdatedManual))
            ->values();

        $progress = function (string $state, int $done) use ($tradeUnit, $toTranslate, &$failed): void {
            TradeUnitNameTranslationProgressEvent::dispatch($tradeUnit, [
                'state'  => $state,
                'done'   => $done,
                'total'  => $toTranslate->count(),
                'failed' => $failed,
            ]);
        };

        if ($toTranslate->isEmpty()) {
            $progress('done', 0);

            return [];
        }

        $english = Language::where('code', 'en')->firstOrFail();
        $progress('running', 0);

        foreach ($toTranslate as $index => $language) {
            $translated = trim(Translate::run($englishName, $english, $language, 'catalogue'));

            // ponytail: the translator hands the English back when it fails, so an unchanged name counts as failed
            if ($translated === '' || $translated === $englishName) {
                $failed[] = $language->code;
            } else {
                $names[$language->code]    = $translated;
                $statuses[$language->code] = ['source' => $englishName, 'auto' => true];
            }

            $progress('running', $index + 1);
        }

        $data                    = (array) $tradeUnit->data;
        $data['name_i8n_status'] = $statuses;

        $tradeUnit->name_i8n = $names;
        $tradeUnit->data     = $data;
        $tradeUnit->save();

        $progress('done', $toTranslate->count());

        return $failed;
    }

    /**
     * A translation with no status was written before the status was kept: it is a person's and
     * its source is unknown, so it is never replaced.
     *
     * @param array{source: string, auto: bool}|null $status
     */
    public function needsTranslation(?string $current, ?array $status, string $englishName, bool $includeOutdatedManual): bool
    {
        if (blank($current)) {
            return true;
        }

        if (!$status || $status['source'] === $englishName) {
            return false;
        }

        return $status['auto'] || $includeOutdatedManual;
    }

    /**
     * A person who read a machine translation and agrees with it makes it theirs: it stops asking for
     * a review and a later English change marks it outdated instead of translating over it.
     *
     * @param array<int, string> $languages
     */
    public function approve(TradeUnit $tradeUnit, array $languages): void
    {
        $data     = (array) $tradeUnit->data;
        $statuses = (array) ($data['name_i8n_status'] ?? []);

        foreach ($languages as $code) {
            if (isset($statuses[$code])) {
                $statuses[$code]['auto'] = false;
            }
        }

        $data['name_i8n_status'] = $statuses;
        $tradeUnit->data         = $data;
        $tradeUnit->save();
    }

    /**
     * What the edit form shows next to each translation.
     *
     * @return array<string, array{state: string, auto: bool}>
     */
    public function getStatuses(TradeUnit $tradeUnit): array
    {
        $englishName = trim((string) $tradeUnit->name);
        $names       = $tradeUnit->getTranslations('name_i8n');
        $statuses    = (array) data_get($tradeUnit->data, 'name_i8n_status', []);
        $result      = [];

        foreach (GetTradeUnitNameTranslationLanguages::run($tradeUnit->group) as $code) {
            $status = $statuses[$code] ?? null;

            $result[$code] = [
                'state' => match (true) {
                    blank($names[$code] ?? null)                           => 'missing',
                    $status !== null && $status['source'] !== $englishName => 'outdated',
                    (bool) ($status['auto'] ?? false)                      => 'review',
                    default                                                => 'ok',
                },
                'auto'  => (bool) ($status['auto'] ?? false),
            ];
        }

        return $result;
    }
}
