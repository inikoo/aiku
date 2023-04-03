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




    case DATA                       = 'data';
    case NAME                       = 'name';

    case EMAIL                      = 'email';
    case PHONE                      = 'phone';
    case IDENTITY_DOCUMENT_TYPE     = 'identity_document_type';
    case IDENTITY_DOCUMENT_NUMBER   = 'identity_document_number';
    case DATE_OF_BIRTH              = 'date_of_birth';

    case GENDER                     = 'gender';

    case WORKER_NUMBER              = 'worker_number';
    case JOB_TITLE                  = 'job_title';
    case EMERGENCY_CONTACT          = 'emergency_contact';
    case HISTORY                    = 'history';



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
                'icon'  => 'fal fa-cubes',
            ],
            EmployeeTabsEnum::PHONE => [
                'title' => __('phone'),
                'icon'  => 'fal fa-money-bill-wave',
            ],
            EmployeeTabsEnum::IDENTITY_DOCUMENT_TYPE => [
                'title' => __('identity document type'),
                'icon'  => 'fal fa-user',
            ],EmployeeTabsEnum::IDENTITY_DOCUMENT_NUMBER => [
                'title' => __('identity document number'),
                'icon'  => 'fal fa-tags',
            ],EmployeeTabsEnum::DATE_OF_BIRTH => [
                'title' => __('date of birth'),
                'icon'  => 'fal fa-bullhorn',
            ],EmployeeTabsEnum::GENDER => [
                'title' => __('gender'),
                'icon'  => 'fal fa-project-diagram',
            ],
            EmployeeTabsEnum::WORKER_NUMBER => [
                'title' => __('worker number'),
                'icon'  => 'fal fa-project-diagram',
            ],
            EmployeeTabsEnum::JOB_TITLE => [
                'title' => __('job title'),
                'icon'  => 'fal fa-project-diagram',
            ],
            EmployeeTabsEnum::EMERGENCY_CONTACT => [
                'title' => __('emergency contact'),
                'icon'  => 'fal fa-project-diagram',
            ],
            EmployeeTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-project-diagram',
            ],
        };
    }
}
