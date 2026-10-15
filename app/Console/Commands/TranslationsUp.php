<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 01 Sept 2026 20:20:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Push the term list and translations to POEditor.
 *
 * Runs t:down first, always. /translations/update OVERWRITES: pushing a stale local file
 * silently destroys whatever a translator fixed in POEditor. Pulling first means the local
 * files already carry their edits, so the upload cannot undo anything. The scan then runs
 * with --merge, so new strings in the code become terms and anything the download dropped
 * comes back; a plain scan would delete whatever it cannot see, such as bare template text.
 *
 * The upload does what translation:upload does (projects/sync with the en.json terms, then
 * translations/update per language) but one request at a time with a line per step, so a
 * slow POEditor answer is visibly in flight rather than looking hung, and a refusal POEditor
 * reports inside an HTTP 200 is shown instead of swallowed. lang/php_*.json is the
 * laravel-vue-i18n build output, not a language, and is left out.
 *
 * One thing this still cannot do: /translations/update will not seed a language that has
 * no translations at all. A brand-new language needs /translations/add, which t:seed calls.
 */
class TranslationsUp extends Command
{
    private const TIMEOUT_SECONDS = 600;

    protected $signature = 't:up {--skip-download : Push without pulling first, only when nobody has touched POEditor}';

    protected $description = 'Pull, repair, then push terms and translations to POEditor';

    public function handle(): int
    {
        if (!$this->option('skip-download')) {
            $this->info('→ t:down (pulling first so the upload cannot overwrite anyone)');

            if ($this->call('t:down') !== self::SUCCESS) {
                $this->error('t:down failed - not uploading a half-repaired set of files');

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('→ translation:scan');

        if ($this->call('translation:scan', ['--merge' => true]) !== self::SUCCESS) {
            $this->error('translation:scan failed - stopping');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('→ upload to POEditor');

        if (!$this->syncTerms()) {
            $this->error('Syncing the terms failed - not uploading translations against a stale term list');

            return self::FAILURE;
        }

        $failedLanguages = $this->syncTranslations();

        $this->newLine();

        if ($failedLanguages->isNotEmpty()) {
            $this->error('Not uploaded: '.$failedLanguages->implode(', ').'. Run t:up --skip-download again once POEditor answers.');

            return self::FAILURE;
        }

        $this->info('Done. Translations for terms POEditor held empty are not sent by the upload: run t:seed <code> for a language whose percentage did not move.');

        return self::SUCCESS;
    }

    private function syncTerms(): bool
    {
        $terms = collect(json_decode(file_get_contents(lang_path(config('translation.base_language').'.json')), true))
            ->keys()
            ->map(fn ($term) => ['term' => $this->normalizeKey((string) $term)])
            ->values();

        $this->output->write(sprintf('  terms: syncing %d terms… ', $terms->count()));

        $response = $this->post('projects/sync', ['data' => $terms->toJson()], $seconds);

        if (!$this->succeeded($response)) {
            $this->line('<error>failed</error> '.$this->failureReason($response));

            return false;
        }

        $this->line(sprintf(
            '<info>done</info> in %ss (added %d, updated %d, deleted %d)',
            $seconds,
            $response->json('result.terms.added', 0),
            $response->json('result.terms.updated', 0),
            $response->json('result.terms.deleted', 0),
        ));

        return true;
    }

    private function syncTranslations(): Collection
    {
        $files = collect(glob(lang_path('*.json')))
            ->reject(fn (string $path) => str_starts_with(basename($path), 'php_'))
            ->values();

        $failedLanguages = collect();

        foreach ($files as $index => $path) {
            $language = basename($path, '.json');
            $entries = collect(json_decode(file_get_contents($path), true))
                ->map(fn ($value, $term) => ['term' => (string) $term, 'translation' => ['content' => $value]])
                ->values();

            $this->output->write(sprintf('  [%d/%d] %s: sending %d translations… ', $index + 1, $files->count(), $language, $entries->count()));

            $response = $this->post('translations/update', ['language' => $language, 'data' => $entries->toJson()], $seconds);

            if (!$this->succeeded($response)) {
                $this->line('<error>failed</error> '.$this->failureReason($response));
                $failedLanguages->push($language);

                continue;
            }

            $this->line(sprintf('<info>done</info> in %ss (updated %d)', $seconds, $response->json('result.translations.updated', 0)));
        }

        return $failedLanguages;
    }

    private function post(string $endpoint, array $parameters, ?float &$seconds): Response|Throwable
    {
        $startedAt = microtime(true);

        try {
            return Http::asForm()->timeout(self::TIMEOUT_SECONDS)->post('https://api.poeditor.com/v2/'.$endpoint, [
                'api_token' => config('translation.api_key'),
                'id' => config('translation.project_id'),
                ...$parameters,
            ]);
        } catch (Throwable $exception) {
            return $exception;
        } finally {
            $seconds = round(microtime(true) - $startedAt, 1);
        }
    }

    private function succeeded(Response|Throwable $response): bool
    {
        return $response instanceof Response && $response->json('response.status') === 'success';
    }

    private function failureReason(Response|Throwable $response): string
    {
        if ($response instanceof Throwable) {
            return $response->getMessage();
        }

        return (string) ($response->json('response.message') ?? 'HTTP '.$response->status());
    }

    private function normalizeKey(string $value): string
    {
        return preg_replace('/\\\\([\'"\\\\`])/', '$1', $value);
    }
}
