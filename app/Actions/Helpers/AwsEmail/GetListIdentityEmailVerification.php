<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:34 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\AwsEmail;

use App\Actions\Comms\EmailAddress\Traits\AwsClient;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use Lorisleiva\Actions\ActionRequest;

class GetListIdentityEmailVerification extends OrgAction
{
    use WithActionUpdate;
    use AwsClient;

    public function handle(): array
    {
        return $this->getSesClient()->listIdentities([
            'IdentityType' => 'EmailAddress',
        ])['Identities'];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function rules(): array
    {
        return [
            'sender_email_address' => ['required', 'string', 'email']
        ];
    }

    public function asController(ActionRequest $request): array
    {
        return $this->handle();
    }
}
