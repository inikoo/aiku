<?php

namespace App\Actions\Goods\TradeUnit;

use App\Actions\OrgAction;
use App\Models\Goods\TradeUnit;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTradeUnitTranslationsFromUpdate extends OrgAction
{
    use asAction;

    public function handle(TradeUnit $tradeUnit, array $modelData): TradeUnit
    {
        $name_i8n = [];
        $description_i8n = [];
        $description_title_i8n = [];
        $description_extra_i8n = [];

        if (Arr::has($modelData, 'translations.name')) {
            $this->stampNamesWrittenByHand($tradeUnit, $modelData['translations']['name']);

            foreach ($modelData['translations']['name'] as $locale => $translation) {
                $name_i8n[$locale] = $translation;
                $tradeUnit->name_i8n = $name_i8n;
            }
        }
        if (Arr::has($modelData, 'translations.description_title')) {
            foreach ($modelData['translations']['description_title'] as $locale => $translation) {
                $description_title_i8n[$locale] = $translation;
                $tradeUnit->description_title_i8n = $description_title_i8n;
            }
        }
        if (Arr::has($modelData, 'translations.description')) {
            foreach ($modelData['translations']['description'] as $locale => $translation) {
                $description_i8n[$locale] = $translation;
                $tradeUnit->description_i8n = $description_i8n;
            }
        }
        if (Arr::has($modelData, 'translations.description_extra')) {
            foreach ($modelData['translations']['description_extra'] as $locale => $translation) {
                $description_extra_i8n[$locale] = $translation;
                $tradeUnit->description_extra_i8n = $description_extra_i8n;
            }
        }

        $tradeUnit->save();


        return $tradeUnit;


    }

    /**
     * A name typed or changed in the form is a person's, made from today's English name, so a later
     * English change marks it outdated instead of translating over it (TranslateTradeUnitName).
     *
     * @param array<string, string|null> $names
     */
    public function stampNamesWrittenByHand(TradeUnit $tradeUnit, array $names): void
    {
        $current  = $tradeUnit->getTranslations('name_i8n');
        $data     = (array) $tradeUnit->data;
        $statuses = (array) Arr::get($data, 'name_i8n_status', []);

        foreach ($names as $locale => $name) {
            if (blank($name)) {
                unset($statuses[$locale]);
            } elseif (($current[$locale] ?? null) !== $name) {
                $statuses[$locale] = ['source' => trim((string) $tradeUnit->name), 'auto' => false];
            }
        }

        $data['name_i8n_status'] = array_intersect_key($statuses, $names);
        $tradeUnit->data         = $data;
    }

    public function rules(): array
    {
        return [
            'translations' => ['required', 'array'],
        ];
    }

    public function action(TradeUnit $tradeUnit, array $modelData): void
    {
        $this->initialisationFromGroup($tradeUnit->group, $modelData);
        $this->handle($tradeUnit, $this->validatedData);
    }

}
