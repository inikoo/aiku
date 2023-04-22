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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreGroup
{
    use AsAction;
    use WithAttributes;

    public string $commandSignature = 'create:group {code} {name} {currency_code}';

    public function handle(array $modelData): Group
    {
        /** @var Group $group */
        $group = Group::create($modelData);

        $dbSchema="aiku_grp_".$group->code;
        DB::statement("CREATE SCHEMA $dbSchema");

        $database_settings = data_get(config('database.connections'), 'group');
        data_set($database_settings, 'search_path', $dbSchema);
        config(['database.connections.group' => $database_settings]);
        DB::connection('group');
        DB::purge('group');

        Artisan::call('migrate:fresh --force --path=database/migrations/group --database=group');


        return $group;
    }

    public function rules(): array
    {
        return [
            'code'        => ['sometimes', 'required', 'unique:groups', 'between:2,6'],
            'name'        => ['sometimes', 'required', 'max:64'],
            'currency_id' => ['sometimes', 'required', 'exists:currencies,id'],
        ];
    }


    public function asAction($modelData): Group
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();
        return $this->handle($validatedData);
    }

    public function asCommand(Command $command): int
    {
        try {
            $currency = Currency::where('code', $command->argument('currency_code'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());
            return 1;
        }
        $this->setRawAttributes([
            'code'        => $command->argument('code'),
            'name'        => $command->argument('name'),
            'currency_id' => $currency->id
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
