<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:10:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\DispatchedEmail;

use App\Enums\EnumHelperTrait;

enum DispatchedEmailStateEnum: string
{
    use EnumHelperTrait;

    case READY = 'ready';
    case SENT_TO_PROVIDER = 'sent_to_provider';

    case ERROR = 'error';
    case REJECTED_BY_PROVIDER = 'rejected_by_provider';

    case SENT = 'sent';

    case DELIVERED = 'delivered';
    case HARD_BOUNCE = 'hard-bounce';
    case SOFT_BOUNCE = 'soft-bounce';
    case OPENED = 'opened';

    case CLICKED = 'clicked';
    case SPAM = 'spam';
    case UNSUBSCRIBED = 'unsubscribed';


    public static function labels(): array
    {
        return [
            'ready'                => __('Ready to send'),
            'sent_to_provider'     => __('Sent to provider'),
            'error'                => __('Error, count not send'),
            'rejected_by_provider' => __('Rejected'),
            'sent'                 => __('Sent'),
            'delivered'            => __('Delivered'),
            'hard-bounce'          => __('Hard bounce'),
            'soft-bounce'          => __('Soft bounce'),
            'opened'               => __('Opened'),
            'clicked'              => __('Clicked'),
            'spam'                 => __('Spam'),
            'unsubscribed'         => __('Unsubscribed'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'ready'                => [

                'tooltip' => __('Ready'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-indigo-500'


            ],
            'sent_to_provider'     => [

                'tooltip' => __('Sent to provider'),
                'icon'    => 'fal fa-paper-plane',
                'class'   => 'text-green-600 animate-pulse'

            ],
            'error'                => [

                'tooltip' => __('Ready'),
                'icon'    => 'fal fa-exclamation-circle',
                'class'   => 'text-red-500'


            ],
            'rejected_by_provider' => [

                'tooltip' => __('Reject, email has a virus'),
                'icon'    => 'fal fa-virus',
                'class'   => 'text-red-500'


            ],
            'sent'                 => [

                'tooltip' => __('sent'),
                'icon'    => 'fal fa-paper-plane',
                'class'   => 'text-green-600 animate-pulse'

            ],
            'delivered'            => [

                'tooltip' => __('delivered'),
                'icon'    => 'fal fa-inbox-in',

            ],
            'hard-bounce'          => [

                'tooltip' => __('hand bounce'),
                'icon'    => 'fal fa-exclamation-triangle',
                'class'   => 'text-red-500'

            ],

            'soft-bounce' => [

                'tooltip' => __('soft bounce'),
                'icon'    => 'fal fa-square',
                'class'   => 'text-orange-500'

            ],

            'opened'  => [
                'tooltip' => __('opened'),
                'icon'    => 'fal fa-envelope-open',
            ],
            'clicked' => [
                'tooltip' => __('clicked'),
                'icon'    => 'fal fa-mouse-pointer',
            ],

            'spam' => [
                'tooltip' => __('spam (complain)'),
                'icon'    => 'fal fa-dumpster',
                'class'   => 'text-red-500'
            ],

            'unsubscribed' => [
                'tooltip' => __('Unsubscribed'),
                'icon'    => 'fal fa-hand-paper',
                'class'   => 'text-red-500'
            ],

        ];
    }
}
