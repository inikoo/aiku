<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum EmployeeTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;




    case NAME                       = 'name';

    case EMAIL                      = 'email';
    case PHONE                      = 'phone';
    case IDENTITY_DOCUMENT_NUMBER   = 'identity_document_number';
    case WORKER_NUMBER              = 'worker_number';
    case JOB_TITLE                  = 'job_title';
    case EMERGENCY_CONTACT          = 'emergency_contact';
    case HISTORY                    = 'history';
    case DATA                       = 'data';
    case GENDER                     = 'gender';
    case DATE_OF_BIRTH              = 'date_of_birth';






    public function blueprint(): array
    {
        return match ($this) {
            EmployeeTabsEnum::DATA => [
                'title' => __('database'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            EmployeeTabsEnum::NAME => [
                'title' => __('name'),
                'icon'  => 'fal fa-signature',
            ],
            EmployeeTabsEnum::EMAIL => [
                'title' => __('email'),
                'icon'  => 'fal fa-envelope',
            ],
            EmployeeTabsEnum::PHONE => [
                'title' => __('phone'),
                'icon'  => 'fal fa-phone',
            ]
            ,EmployeeTabsEnum::IDENTITY_DOCUMENT_NUMBER => [
                'title' => __('identity document number'),
                'icon'  => 'fal fa-id-card',
            ],EmployeeTabsEnum::DATE_OF_BIRTH => [
                'title' => __('date of birth'),
                'icon'  => 'fal fa-birthday-cake',
                'type'  => 'icon',
                'align' => 'right',
            ],EmployeeTabsEnum::GENDER => [
                'title' => __('gender'),
                'icon'  => 'fal fa-venus-mars',
                'type'  => 'icon',
                'align' => 'right',
            ],
            EmployeeTabsEnum::WORKER_NUMBER => [
                'title' => __('worker number'),
                'icon'  => 'fal fa-hashtag',
            ],
            EmployeeTabsEnum::JOB_TITLE => [
                'title' => __('job title'),
                'icon'  => 'fal fa-heading',
            ],
            EmployeeTabsEnum::EMERGENCY_CONTACT => [
                'title' => __('emergency contact'),
                'icon'  => 'fal fa-hospital-user',
            ],
            EmployeeTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
