<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Apr 2023 08:40:49 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Group;

use App\Models\Assets\Currency;
use Illuminate\Console\Command;
use App\Models\Central\Group;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreGroup
{

    use AsAction;
    use WithAttributes;

    public string $commandSignature = 'create:group {code} {name} {currency_code}';

    public function handle(array $modelData)
    {
        $currency = Currency::where('code', $modelData['currency'])->first();

        return Group::create([
            'code' => $modelData['code'],
            'name' => $modelData['name'],
            'currency_id' => $currency->id
        ]);
    }

    public function rules()
    {
        return [
            'code' => ['sometimes', 'required', 'unique:groups', 'between:2,3'],
            'name' => ['sometimes', 'required', 'max:64'],
            'currency' => ['sometimes', 'required', 'exists:currencies,code'],
        ];
    }

    public function asCommand(Command $command): void
    {
        $this->setRawAttributes([
            'code' => $command->argument('code'),
            'name' => $command->argument('name'),
            'currency' => $command->argument('currency_code')
        ]);

        $validatedData = $this->validateAttributes();

        $this->handle($validatedData);

        $command->info('Done!');
    }
}
