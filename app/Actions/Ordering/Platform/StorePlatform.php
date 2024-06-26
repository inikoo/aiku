<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Platform;

use App\Models\Ordering\Platform;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePlatform
{
    use AsAction;
    use WithAttributes;

    public string $commandSignature = 'platform:store {code}';

    public function handle(array $modelData): Platform
    {
        /** @var Platform $platform */
        $platform = Platform::create($modelData);

        return $platform;
    }

    public function rules(): array
    {
        return [
            'code' => ['string', 'required', Rule::unique('platforms', 'code')],
            'name' => ['string', 'required']
        ];
    }

    public function action(array $modelData): Platform
    {
        return $this->handle($modelData);
    }

    public function asCommand(Command $command): int
    {
        $code = $command->argument('code');

        $modelData = [
            'code' => $code,
            'name' => Str::ucfirst($code)
        ];

        $this->handle($modelData);

        echo "Success create the platform $code ✅ \n";

        return 0;
    }
}
