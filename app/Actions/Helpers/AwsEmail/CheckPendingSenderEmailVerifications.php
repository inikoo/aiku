<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 12 Dec 2023 13:14:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\AwsEmail;

use App\Actions\Comms\EmailAddress\Traits\AwsClient;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\SenderEmail\SenderEmailStateEnum;
use App\Models\Comms\SenderEmail;
use Lorisleiva\Actions\Concerns\AsCommand;

class CheckPendingSenderEmailVerifications
{
    use WithActionUpdate;
    use AwsClient;
    use AsCommand;


    public function handle(): void
    {

        SenderEmail::where('state', SenderEmailStateEnum::PENDING)->get()->each(function (SenderEmail $senderEmail) {
            $email = $senderEmail->email_address;

            $state = GetEmailSesVerificationState::run($email);

            if ($state == SenderEmailStateEnum::PENDING || $state == SenderEmailStateEnum::ERROR) {
                return;
            }

            data_set($modelData, 'state', $state);

            if ($state == SenderEmailStateEnum::VERIFIED) {
                if ($senderEmail->verified_at === null) {
                    data_set($modelData, 'verified_at', now());
                }
            }

            $senderEmail->update($modelData);

        });
    }

    public string $commandSignature = 'aws:check-pending-sender-email-verifications';


    public function asCommand(): int
    {
        $this->handle();


        return 0;
    }
}
