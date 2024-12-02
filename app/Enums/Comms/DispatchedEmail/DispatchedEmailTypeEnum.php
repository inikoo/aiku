<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:10:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\DispatchedEmail;

use App\Enums\EnumHelperTrait;

enum DispatchedEmailTypeEnum: string
{
    use EnumHelperTrait;

    case MARKETING = 'marketing';
    case TRANSACTIONAL = 'transactional';
    case INVITE = 'invite';
    case TEST = 'test';


    public static function labels(): array
    {
        return [
            'marketing'     => __('Marketing'),
            'transactional' => __('Transactional'),
            'test'          => __('Test'),
            'invite'        => __('Invite')

        ];
    }

}
