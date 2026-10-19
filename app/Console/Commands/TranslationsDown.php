<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 01 Sept 2026 20:20:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Pull translations from POEditor, which is the source of truth, and repair them.
 * Each step exists because the one before it breaks something:
 *
 *  1. translation:download - replaces every lang/<code>.json POEditor knows about,
 *                            wholesale. It also drops terms POEditor has not got yet.
 *  2. keep local strings   - puts back every local translation the download dropped or
 *                            exported empty, so a string added by hand to lang/<code>.json
 *                            survives the pull. POEditor still wins wherever it holds a
 *                            translation; a pull only adds and changes, it never deletes.
 *  3. lang:strip-empty     - the export carries an empty string for every untranslated
 *                            term, and laravel-vue-i18n renders "" as a blank label.
 *  4. lang:apply-glossary  - restores the business terms machine translation mangles,
 *                            without touching anything a translator has edited.
 *
 * A string kept in step 2 is still local only. It is listed at the end with the t:seed
 * call that sends it to POEditor, after t:up has made it a term. The cost is that a term
 * deleted from POEditor stays in the local files until it is removed from them by hand.
 */
class TranslationsDown extends Command
{
    private const DOWNLOAD_JSON_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;

    protected $signature = 't:down {--skip-download : Repair and rescan the local files without pulling}';

    protected $description = 'Pull translations from POEditor and repair them (download, keep local strings, strip empties, apply glossary)';

    public function handle(): int
    {
        $keptByLocale = [];

        if (!$this->option('skip-download')) {
            $localTranslations = $this->readLocalTranslations();

            $this->newLine();
            $this->info('→ translation:download');

            if ($this->call('translation:download') !== self::SUCCESS) {
                $this->error('translation:download failed - stopping so the next step cannot make it worse');

                return self::FAILURE;
            }

            $this->newLine();
            $this->info('→ keep local strings POEditor has not got');
            $keptByLocale = $this->keepLocalTranslations($localTranslations);
        }

        foreach (['lang:strip-empty', 'lang:apply-glossary'] as $command) {
            $this->newLine();
            $this->info("→ {$command}");

            if ($this->call($command) !== self::SUCCESS) {
                $this->error("{$command} failed - stopping so the next step cannot make it worse");

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('Done. Review `git diff lang/` before committing.');
        $this->line('New strings in the code are not terms yet - t:up scans and pushes them.');
        $this->reportKeptTranslations($keptByLocale);

        return self::SUCCESS;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function readLocalTranslations(): array
    {
        $localTranslations = [];

        foreach (glob(lang_path('*.json')) as $path) {
            $locale = basename($path, '.json');

            if (str_starts_with($locale, 'php_')) {
                continue;
            }

            $strings = json_decode(file_get_contents($path), true);

            if (is_array($strings)) {
                $localTranslations[$locale] = array_filter($strings, fn ($value) => is_string($value) && trim($value) !== '');
            }
        }

        return $localTranslations;
    }

    /**
     * @param  array<string, array<string, string>>  $localTranslations
     * @return array<string, int>
     */
    private function keepLocalTranslations(array $localTranslations): array
    {
        $keptByLocale = [];

        foreach ($localTranslations as $locale => $localStrings) {
            $path = lang_path($locale.'.json');
            $downloaded = is_file($path) ? json_decode(file_get_contents($path), true) : [];

            if (!is_array($downloaded)) {
                $downloaded = [];
            }

            $missing = array_filter(
                $localStrings,
                fn ($value, $term) => !is_string($downloaded[$term] ?? null) || trim($downloaded[$term]) === '',
                ARRAY_FILTER_USE_BOTH
            );

            if ($missing === []) {
                continue;
            }

            $merged = array_replace($downloaded, $missing);
            ksort($merged);
            file_put_contents($path, json_encode($merged, self::DOWNLOAD_JSON_FLAGS));

            $keptByLocale[$locale] = count($missing);
        }

        return $keptByLocale;
    }

    /**
     * @param  array<string, int>  $keptByLocale
     */
    private function reportKeptTranslations(array $keptByLocale): void
    {
        if ($keptByLocale === []) {
            return;
        }

        $this->newLine();
        $this->warn('Kept local strings POEditor has not got - the next pull keeps them too, but translators never see them:');
        $this->table(
            ['locale', 'kept'],
            collect($keptByLocale)->map(fn (int $count, string $locale) => [$locale, number_format($count)])->values()->all()
        );

        $locales = collect($keptByLocale)->keys()->reject(fn (string $locale) => $locale === config('translation.base_language'));

        if ($locales->isNotEmpty()) {
            $this->line('Send them to POEditor: php artisan t:up, then php artisan t:seed '.$locales->implode(' '));
        }
    }
}
