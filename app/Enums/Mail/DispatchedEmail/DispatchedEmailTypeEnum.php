<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 03 Nov 2024 13:25:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Mail\DispatchedEmail;

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
