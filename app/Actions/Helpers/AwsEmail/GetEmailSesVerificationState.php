<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:34 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\AwsEmail;

use App\Actions\Comms\EmailAddress\Traits\AwsClient;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\SenderEmail\SenderEmailStateEnum;
use Arr;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;

class GetEmailSesVerificationState
{
    use WithActionUpdate;
    use AwsClient;
    use AsCommand;

    public string $commandSignature = 'aws:get-email-verification {email}}';

    public function handle(string $email): SenderEmailStateEnum
    {
        try {
            $result = $this->getSesClient()->getIdentityVerificationAttributes(
                [
                    'Identities' => [
                        $email,
                        explode('@', $email)[1]
                    ]
                ]
            );

            $result = Arr::get($result, 'VerificationAttributes', []);
            if (count($result) === 0) {
                return SenderEmailStateEnum::VERIFICATION_NOT_SUBMITTED;
            }

            foreach ($result as $identity => $data) {
                if ($identity === $email) {
                    if ($data['VerificationStatus'] === 'Success') {
                        return SenderEmailStateEnum::VERIFIED;
                    } elseif ($data['VerificationStatus'] === 'Pending') {
                        return SenderEmailStateEnum::PENDING;
                    }

                    return SenderEmailStateEnum::FAIL;
                }
            }

            // verify the domain
            foreach ($result as $data) {
                if ($data['VerificationStatus'] === 'Success') {
                    return SenderEmailStateEnum::VERIFIED;
                } elseif ($data['VerificationStatus'] === 'Pending') {
                    return SenderEmailStateEnum::PENDING;
                }
            }
        } catch (Exception) {
            return SenderEmailStateEnum::FAIL;
        }

        return SenderEmailStateEnum::FAIL;
    }


    public function asCommand(Command $command): int
    {
        $result = $this->handle($command->argument('email'));

        $command->info($result->value);

        return 0;
    }
}
