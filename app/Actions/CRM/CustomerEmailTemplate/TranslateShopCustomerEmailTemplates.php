<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\CustomerEmailTemplate;

use App\Actions\Helpers\Translations\Translate;
use App\Models\Catalogue\Shop;
use App\Models\CRM\CustomerEmailTemplate;
use App\Models\Helpers\Language;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Translates the shop's copies of the default emails it has not edited yet, checked again at
 * the moment of writing, since staff can save a template while the translation runs. A text whose
 * {placeholders} did not survive the translation stays in English, since a broken
 * placeholder would reach the customer as a literal word.
 */
class TranslateShopCustomerEmailTemplates
{
    use AsAction;

    public string $jobQueue = 'translate';

    private const string BRIEF = 'This is an email a wholesale shop sends to one of its trade customers. Keep every placeholder in curly braces, such as {contact_name}, exactly as written, untranslated. Keep the line breaks.';

    public function handle(Shop $shop): void
    {
        $english = Language::where('code', 'en')->firstOrFail();

        foreach ($shop->customerEmailTemplates()->whereNull('updated_by_user_id')->get() as $template) {
            $translation = [
                'name'    => $this->translate($template->name, $english, $shop->language),
                'subject' => $this->translate($template->subject, $english, $shop->language),
                'body'    => $this->translate($template->body, $english, $shop->language),
            ];

            CustomerEmailTemplate::where('id', $template->id)->whereNull('updated_by_user_id')->update($translation);
        }
    }

    private function translate(string $text, Language $from, Language $to): string
    {
        $translated = Translate::run($text, $from, $to, brief: self::BRIEF);

        return self::placeholdersIn($translated) === self::placeholdersIn($text) ? $translated : $text;
    }

    /**
     * @return array<int, string>
     */
    public static function placeholdersIn(string $text): array
    {
        preg_match_all('/\{[a-z_]+}/', $text, $matches);
        $placeholders = array_unique($matches[0]);
        sort($placeholders);

        return $placeholders;
    }
}
