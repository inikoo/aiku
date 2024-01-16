<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 27 Oct 2023 19:58:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\SenderEmail;

use App\Actions\Helpers\AwsEmail\GetEmailSesVerificationState;
use App\Actions\Helpers\AwsEmail\SendIdentityEmailVerification;
use App\Models\Mail\SenderEmail;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreSenderEmail
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    private string $scope;
    private array $queryRules;

    public function handle(array $modelData): SenderEmail
    {

        data_set(
            $modelData,
            'state',
            GetEmailSesVerificationState::run($modelData['email_address'])
        );

        $senderEmail= SenderEmail::create($modelData);

        return SendIdentityEmailVerification::run($senderEmail);
    }

    public function action(array $objectData): SenderEmail
    {
        $this->asAction = true;

        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }

    public function rules(): array
    {
        return [

            'email_address' => ['required', 'email', 'iunique:sender_emails,email_address'],
        ];
    }


}
