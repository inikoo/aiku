<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:34 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Mail\EmailAddress\Traits\AwsClient;
use App\Actions\Mail\SenderEmail\StoreSenderEmail;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Mail\SenderEmail;
use App\Models\Market\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateProspectsMailshotSetting
{
    use WithActionUpdate;
    use AwsClient;

    private bool $asAction = false;

    public function handle(Shop $shop, array $modelData): JsonResponse|Shop
    {


        if ($senderEmailAddress = Arr::get($modelData, 'prospects_sender_email_address')) {
            Arr::forget($modelData, 'prospects_sender_email_address');

            if (!$senderEmail = SenderEmail::where('email_address', $senderEmailAddress)->first()) {
                $senderEmail = StoreSenderEmail::make()->action(
                    [
                        'email_address' => $senderEmailAddress,
                    ]
                );
            }
            data_set($modelData, 'prospects_sender_email_id', $senderEmail->id);
        }

        if (Arr::get($modelData, 'title')) {
            data_set($modelData, 'settings.mailshot.unsubscribe.title', Arr::get($modelData, 'title'));
            Arr::forget($modelData, 'title');
        }

        if (Arr::get($modelData, 'description')) {
            data_set($modelData, 'settings.mailshot.unsubscribe.description', Arr::get($modelData, 'description'));
            Arr::forget($modelData, 'description');
        }

        return $this->update($shop, $modelData, ['data', 'settings']);
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
            'title'                          => ['sometimes', 'required', 'string', 'max:255'],
            'description'                    => ['sometimes', 'required', 'string', 'max:255'],
            'prospects_sender_email_address' => ['sometimes', 'nullable', 'email']
        ];
    }

    public function asController(Shop $shop, ActionRequest $request): JsonResponse|Shop
    {

        $this->fillFromRequest($request);
        $modelData = $this->validateAttributes();


        return $this->handle(
            shop: $shop,
            modelData: $modelData
        );
    }
}
