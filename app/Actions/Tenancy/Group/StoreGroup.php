<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Group;

use App\Models\Assets\Currency;
use App\Models\Tenancy\Group;
use Exception;
use Illuminate\Console\Command;
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
