<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Notifications;

use App\Actions\Firebase\CheckUserStatusFirebase;
use App\Models\Auth\User;
use App\Models\Tenancy\Tenant;
use App\Notifications\MeasurementSharedNotification;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsObject;

class PublishPushNotification
{
    use AsObject;
    use AsAction;

    public string $commandSignature   = 'push:publish';
    public string $commandDescription = 'Publish push notification';

    public function handle(Model $model, $content): void
    {
        $model->notify(new MeasurementSharedNotification($content));
    }
}
