<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jul 2024 10:16:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Tiktok;

use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\TiktokUser;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateTiktokUser extends RetinaAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(TiktokUser $tiktokUser, array $modelData)
    {
        return $this->update($tiktokUser, $modelData);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'access_token' => ['sometimes', 'string'],
            'access_token_expire_in' => ['sometimes'],
            'refresh_token' => ['sometimes', 'string'],
            'refresh_token_expire_in' => ['sometimes']
        ];
    }

    public function action(TiktokUser $tiktokUser, array $modelData): void
    {
        $this->initialisationActions($tiktokUser->customer, $modelData);

        $this->handle($tiktokUser, $this->validatedData);
    }
}
