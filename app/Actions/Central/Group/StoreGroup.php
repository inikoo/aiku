<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Apr 2023 08:40:49 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Group;

use App\Models\Assets\Currency;
use Exception;
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
            'code'        => $modelData['code'],
            'name'        => $modelData['name'],
            'currency_id' => $currency->id
        ]);
    }

    public function rules(): array
    {
        return [
            'code'     => ['sometimes', 'required', 'unique:groups', 'between:2,3'],
            'name'     => ['sometimes', 'required', 'max:64'],
            'currency' => ['sometimes', 'required', 'exists:currencies,code'],
        ];
    }

    public function asCommand(Command $command): int
    {
        $this->setRawAttributes([
            'code'     => $command->argument('code'),
            'name'     => $command->argument('name'),
            'currency' => $command->argument('currency_code')
        ]);

        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());
            return 1;
        }

        $this->handle($validatedData);

        $command->info('Done!');
        return 0;
    }
}
