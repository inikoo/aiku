<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum GuestTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;




    case NAME                       = 'name';

    case EMAIL                      = 'email';
    case PHONE                      = 'phone';
    case IDENTITY_DOCUMENT_NUMBER   = 'identity_document_number';
    case GENDER                     = 'gender';
    case DATE_OF_BIRTH              = 'date_of_birth';
    case HISTORY                    = 'history';
    case DATA                       = 'data';






    public function blueprint(): array
    {
        return match ($this) {
            GuestTabsEnum::DATA => [
                'title' => __('database'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            GuestTabsEnum::NAME => [
                'title' => __('name'),
                'icon'  => 'fal fa-signature',
            ],
            GuestTabsEnum::EMAIL => [
                'title' => __('email'),
                'icon'  => 'fal fa-envelope',
            ],
            GuestTabsEnum::PHONE => [
                'title' => __('phone'),
                'icon'  => 'fal fa-phone',
            ]
            ,GuestTabsEnum::IDENTITY_DOCUMENT_NUMBER => [
                'title' => __('identity document number'),
                'icon'  => 'fal fa-id-card',
            ],GuestTabsEnum::DATE_OF_BIRTH => [
                'title' => __('date of birth'),
                'icon'  => 'fal fa-birthday-cake',
                'type'  => 'icon',
                'align' => 'right',
            ],GuestTabsEnum::GENDER => [
                'title' => __('gender'),
                'icon'  => 'fal fa-venus-mars',
                'type'  => 'icon',
                'align' => 'right',
            ],
            GuestTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
