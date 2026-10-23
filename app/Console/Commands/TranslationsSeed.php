<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 12:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

/**
 * Sends the translations of lang/<code>.json that POEditor does not hold yet.
 *
 * translation:upload, and so t:up, calls /translations/update, which only changes terms
 * that already have a translation in that language: it answers `success` and leaves every
 * untranslated term empty. A language translated locally from 31% to 100% therefore stays
 * at 31% in POEditor after t:up, and the next t:down wipes the local work. /translations/add
 * is the endpoint that fills the empty terms, and it leaves the translated ones alone.
 *
 * Only the translations POEditor holds empty are sent, read from an export filtered to
 * untranslated terms, so a seed of a few new strings takes seconds instead of sending
 * every string of the file in paced batches.
 *
 * Run t:up --skip-download first so the terms exist, then this.
 */
class TranslationsSeed extends Command
{
    private const BATCH = 800;

    private const PAUSE_SECONDS = 21;

    protected $signature = 't:seed {locales* : Language codes to seed, e.g. sk}';

    protected $description = 'Add to POEditor the translations it does not hold yet for a language';

    public function handle(): int
    {
        $failed = false;

        foreach ($this->argument('locales') as $locale) {
            $path = lang_path($locale.'.json');

            if (!is_file($path)) {
                $this->error("lang/{$locale}.json does not exist");
                $failed = true;

                continue;
            }

            $failed = !$this->seed($locale, $path) || $failed;
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function seed(string $locale, string $path): bool
    {
        $untranslatedTerms = $this->untranslatedTerms($locale);

        if ($untranslatedTerms === null) {
            return false;
        }

        $local = collect(json_decode(file_get_contents($path), true))
            ->filter(fn ($value) => is_string($value) && trim($value) !== '');

        $entries = $local
            ->filter(fn ($value, $term) => isset($untranslatedTerms[(string) $term]) || isset($untranslatedTerms[trim((string) $term)]))
            ->map(fn ($value, $term) => ['term' => (string) $term, 'translation' => ['content' => $value]])
            ->values();

        if ($entries->isEmpty()) {
            $this->info("→ {$locale}: nothing to seed, POEditor holds every translation of the ".number_format($local->count())." local strings");

            return true;
        }

        $batches = $entries->chunk(self::BATCH)->values();
        $this->info("→ {$locale}: {$entries->count()} translations POEditor holds empty, in {$batches->count()} batches");

        $added = 0;
        $succeeded = true;

        foreach ($batches as $index => $batch) {
            $response = Http::asForm()->timeout(120)->post('https://api.poeditor.com/v2/translations/add', [
                'api_token' => config('translation.api_key'),
                'id' => config('translation.project_id'),
                'language' => $locale,
                'data' => $batch->values()->toJson(),
            ]);

            if ($response->json('response.status') !== 'success') {
                $this->error(sprintf('  batch %d/%d refused: %s', $index + 1, $batches->count(), $response->json('response.message') ?? $response->status()));
                $succeeded = false;
            } else {
                $batchAdded = (int) $response->json('result.translations.added', 0);
                $added += $batchAdded;
                $this->line(sprintf('  batch %d/%d: added %d', $index + 1, $batches->count(), $batchAdded));
            }

            if ($index + 1 < $batches->count()) {
                Sleep::for(self::PAUSE_SECONDS)->seconds();
            }
        }

        $this->info("{$locale}: {$added} translations added to POEditor");
        $this->line('A shortfall is usually whitespace-variant keys POEditor normalised away.');

        return $succeeded;
    }

    /**
     * @return array<string, true>|null
     */
    private function untranslatedTerms(string $locale): ?array
    {
        $response = Http::asForm()->timeout(120)->post('https://api.poeditor.com/v2/projects/export', [
            'api_token' => config('translation.api_key'),
            'id' => config('translation.project_id'),
            'language' => $locale,
            'type' => 'key_value_json',
            'filters' => 'untranslated',
        ]);

        if ($response->json('response.status') !== 'success') {
            $this->error("{$locale}: could not list the terms POEditor holds empty: ".($response->json('response.message') ?? $response->status()));

            return null;
        }

        $export = Http::timeout(120)->get($response->json('result.url'))->json();

        if (!is_array($export)) {
            $this->error("{$locale}: the export of the terms POEditor holds empty could not be read");

            return null;
        }

        return collect($export)
            ->mapWithKeys(fn ($entry, $term) => [(string) (is_array($entry) ? array_key_first($entry) : $term) => true])
            ->all();
    }
}
