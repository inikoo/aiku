<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 31 Dec 2024 01:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser\Hydrators;

use App\Models\CRM\WebUser;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class WebUserHydrateApiTokens
{
    use AsAction;

    private WebUser $webUser;

    public function __construct(WebUser $webUser)
    {
        $this->webUser = $webUser;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->webUser->id))->dontRelease()];
    }

    public function handle(WebUser $webUser): void
    {
        $webUser->update(
            [
                'number_api_tokens' => $webUser->tokens->count()
            ]
        );
    }



}
