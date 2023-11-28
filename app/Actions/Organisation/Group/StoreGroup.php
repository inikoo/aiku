<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Group;

use App\Actions\Mail\Mailroom\StoreMailroom;
use App\Actions\Organisation\Group\Hydrators\GroupHydrateJobPositions;
use App\Enums\Mail\Mailroom\MailroomCodeEnum;
use App\Models\Assets\Currency;
use App\Models\Organisation\Group;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreGroup
{
    use AsAction;
    use WithAttributes;

    public string $commandSignature = 'create:group {code} {name} {currency_code}';

    public function handle(array $modelData): Group
    {

        data_set($modelData, 'ulid', Str::ulid());

        /** @var Group $group */
        $group = Group::create($modelData);

        $group->procurementStats()->create();
        $group->humanResourcesStats()->create();

        foreach (MailroomCodeEnum::cases() as $case) {
            StoreMailroom::run(
                [
                    'code' => $case->value
                ]
            );
        }

        GroupHydrateJobPositions::run($group);
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


    public function action($modelData): Group
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
