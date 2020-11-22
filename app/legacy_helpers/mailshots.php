<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 21 Nov 2020 14:19:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\Notifications\Email;
use App\Models\Notifications\EmailService;
use App\Models\Notifications\EmailTemplate;
use App\Models\Notifications\EmailTracking;
use App\Models\Notifications\Mailshot;
use App\Models\Notifications\PublishedEmailTemplate;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function relocate_mailshots($tenant, $legacy_data) {


    $mailshot_data = fill_legacy_data(
        [


        ], $legacy_data
    );


    $mailshot_settings = fill_legacy_data(
        [

        ], $legacy_data
    );


    $emailServiceId = null;
    $emailService   = EmailService::firstWhere('legacy_id', $legacy_data->{'Email Campaign Email Template Type Key'});
    if ($emailService) {
        $emailServiceId = $emailService->id;
    }

    if (!$emailServiceId) {
        return false;
    }


    return (new Mailshot)->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Email Campaign Key'},

        ], [
            'tenant_id'        => $tenant->id,
            'email_service_id' => $emailServiceId,
            'name'             => $legacy_data->{'Email Campaign Name'},
            'state'            => Str::snake($legacy_data->{'Email Campaign State'}),
            'data'             => $mailshot_data,
            'settings'         => $mailshot_settings,
            'created_at'       => $legacy_data->{'Email Campaign Creation Date'},
        ]
    );
}

function relocate_email_template($tenant, $legacy_data) {


    $email_template_data = fill_legacy_data(
        [

        ], $legacy_data
    );


    $email_template_data['template']['html'] = json_decode($legacy_data->{'Email Template Editing JSON'});

    $emailServiceId = null;
    $emailService   = EmailService::firstWhere('legacy_id', $legacy_data->{'Email Template Email Campaign Type Key'});
    if ($emailService) {
        $emailServiceId = $emailService->id;
    }


    $name = $legacy_data->{'Email Template Subject'};
    if ($name == '') {
        $name = $legacy_data->{'Email Template Name'};
    }


    $created_at = $legacy_data->{'Email Template Created'};
    if ($created_at == '') {
        $created_at = $legacy_data->{'Email Template Last Edited'};
    }

    if (!$emailServiceId) {
        return false;
    }

    return (new EmailTemplate())->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Email Template Key'},

        ], [
            'tenant_id'        => $tenant->id,
            'email_service_id' => $emailServiceId,
            'name'             => $name,
            'data'             => $email_template_data,
            'created_at'       => $created_at,
        ]
    );
}

function relocate_published_email_template($tenant, $legacy_data) {


    $published_email_template_data = fill_legacy_data(
        [


        ], $legacy_data
    );


    $emailTemplateId = null;
    $emailServiceId  = null;
    $emailTemplate   = EmailTemplate::firstWhere('legacy_id', $legacy_data->{'Published Email Template Email Template Key'});
    if ($emailTemplate) {
        $emailTemplateId = $emailTemplate->id;
        $emailServiceId  = $emailTemplate->email_service_id;
    }

    if ($emailServiceId == null) {


        $sql = " `Email Tracking Email Template Type Key` from `Email Tracking Dimension`  where `Email Tracking Published Email Template Key`=?  limit 1";
        foreach (DB::connection('legacy')->select("select $sql", [$legacy_data->{'Published Email Template Key'}]) as $_legacy_data) {


            $emailTemplate = EmailTemplate::firstWhere('legacy_id', $_legacy_data->{'Email Tracking Email Template Type Key'});
            if ($emailTemplate) {
                $emailTemplateId = $emailTemplate->id;
                $emailServiceId  = $emailTemplate->email_service_id;
            }


        }
    }

    if ($emailServiceId == null) {
        return false;
    }


    return (new PublishedEmailTemplate())->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Published Email Template Key'},

        ], [
            'tenant_id'         => $tenant->id,
            'email_template_id' => $emailTemplateId,
            'email_service_id'  => $emailServiceId,
            'data'              => $published_email_template_data,
            'created_at'        => $legacy_data->{'Published Email Template From'},
        ]
    );
}


function relocate_email_tracking($tenant, $legacy_data) {


    if ($legacy_data->{'Email Tracking Email'} == '') {
        return false;
    }

    $email_tracking_data = fill_legacy_data(
        [
            'timeline.sent'          => 'Email Tracking Sent Date',
            'timeline.first_view'    => 'Email Tracking First Read Date',
            'timeline.last_view'     => 'Email Tracking Last Read Date',
            'timeline.first_clicked' => 'Email Tracking Last Clicked Date',
            'timeline.last_clicked'  => 'Email Tracking Last Clicked Date',
            'views'                  => 'Email Tracking Number Reads',
            'clicks'                 => 'Email Tracking Number Reads'

        ], $legacy_data
    );

    $email = (new Email())->updateOrCreate(
        [
            'email' => $legacy_data->{'Email Tracking Email'},

        ], []
    );

    if ($legacy_data->{'Email Tracking Email Mailshot Key'}) {
        $mailshot    = Mailshot::firstWhere('legacy_id', $legacy_data->{'Email Tracking Email Mailshot Key'});
        $parent_type = 'Mailshot';
        $parent_id   = $mailshot->id;
    } else {
        $emailService = EmailService::firstWhere('legacy_id', $legacy_data->{'Email Tracking Email Template Type Key'});
        if (!$emailService) {
            print "Cant find $emailService\n";
            dd($legacy_data);
        }
        $parent_type = 'EmailService';
        $parent_id   = $emailService->id;
    }


    if ($legacy_data->{'Email Tracking Recipient'} == 'Customer') {
        $customer       = Customer::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Email Tracking Recipient Key'});
        $recipient_type = 'Customer';
        $recipient_id   = $customer->id;
    } elseif ($legacy_data->{'Email Tracking Recipient'} == 'Prospect') {
        $prospects      = Prospect::firstWhere('legacy_id', $legacy_data->{'Email Tracking Recipient Key'});
        $recipient_type = 'Prospect';
        $recipient_id   = $prospects->id;
    } elseif ($legacy_data->{'Email Tracking Recipient'} == 'User') {
        $user           = (new User)->firstWhere('legacy_id', $legacy_data->{'Email Tracking Recipient Key'});
        $recipient_type = 'User';
        $recipient_id   = $user->id;
    } elseif ($legacy_data->{'Email Tracking Recipient'} == '') {
        $recipient_type = 'Email';
        $recipient_id   = $email->id;
    } else {
        print_r($legacy_data);
        exit("recipient_type no type\n");
    }

    $published_email_template_id = null;
    if ($legacy_data->{'Email Tracking Published Email Template Key'} > 0) {
        $published_email_template    = PublishedEmailTemplate::firstWhere('legacy_id', $legacy_data->{'Email Tracking Published Email Template Key'});
        $published_email_template_id = $published_email_template->id;
    }


    return (new EmailTracking())->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Email Tracking Key'},

        ], [
            'tenant_id'                   => $tenant->id,
            'email_id'                    => $email->id,
            'parent_type'                 => $parent_type,
            'state'                       => Str::snake(strtolower($legacy_data->{'Email Tracking State'})),
            'published_email_template_id' => $published_email_template_id,
            'parent_id'                   => $parent_id,
            'sender_id'                   => $legacy_data->{'Email Tracking SES Id'},
            'recipient_type'              => $recipient_type,
            'recipient_id'                => $recipient_id,
            'data'                        => $email_tracking_data,
            'created_at'                  => $legacy_data->{'Email Tracking Created Date'},
        ]
    );
}


