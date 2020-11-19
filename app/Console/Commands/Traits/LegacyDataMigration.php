<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 29 Sep 2020 17:21:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands\Traits;

use App\Models\Helpers\Attachment;
use App\Models\Helpers\AttachmentModel;
use Illuminate\Support\Facades\DB;

trait LegacyDataMigration {

    public $tenant;

    public function set_legacy_connection($database_name) {

        $database_settings = data_get(config('database.connections'), 'mysql');
        data_set($database_settings, 'database', $database_name);

        config(['database.connections.legacy' => $database_settings]);
        DB::connection('legacy');
        DB::purge('legacy');

    }















    function get_attachments_data($params) {

        $attachment_table    = '`Attachment Bridge` B ';
        $attachmentModelData = [];


        foreach (
            DB::connection('legacy')->select(
                "select * from $attachment_table left join `Attachment Dimension` A on (A.`Attachment Key`=B.`Attachment Key`) where `Subject`=? and `Subject Key`=? ",

                [
                    $params['object'],
                    $params['object_key']
                ]
            ) as $attachment_legacy_data
        ) {


            $attachment_data = fill_legacy_data(
                [
                    'mime_type' => 'Attachment MIME Type',


                ], $attachment_legacy_data
            );


            $attachment = (new Attachment)->updateOrCreate(
                [
                    'legacy_id' => $attachment_legacy_data->{'Attachment Key'}
                ], [
                    'tenant_id' => $this->tenant->id,
                    'checksum'  => $attachment_legacy_data->{'Attachment File Checksum'},
                    'filesize'  => $attachment_legacy_data->{'Attachment File Size'},

                    'attachment_data' => pg_escape_bytea($attachment_legacy_data->{'Attachment Data'}),

                    'data' => $attachment_data
                ]
            );


            $attachmentModelData[] = [
                'attachment_id' => $attachment->id,
                'scope'         => $attachment_legacy_data->{'Attachment Subject Type'},
                'data'          => [
                    'notes' => $attachment_legacy_data->{'Attachment Caption'},
                    'filename'    => $attachment_legacy_data->{'Attachment File Original Name'}
                ]
            ];


        }

        return $attachmentModelData;

    }

    function sync_attachments($model, $attachmentsModelData, $get_scope) {

        $old_attachmentModelIds = [];
        $new_attachmentModelIds = [];

        $model->attachments()->get()->each(
            function ($attachmentModel) use (&$old_attachmentModelIds) {
                $old_attachmentModelIds[] = $attachmentModel->id;
            }
        );
        foreach ($attachmentsModelData as $attachmentModelData) {


            $scope = $get_scope($attachmentModelData['scope']);

            $attachmentModel          = (new AttachmentModel)->updateOrCreate(
                [
                    'attachmentable_type' => $model->getMorphClass(),
                    'attachmentable_id'   => $model->id,
                    'scope'               => $scope,
                    'attachment_id'       => $attachmentModelData['attachment_id'],

                ], [
                    'data' => $attachmentModelData['data'],
                ]
            );
            $new_attachmentModelIds[] = $attachmentModel->id;
            $model->attachments()->save($attachmentModel);

        }
        $model->attachments()->whereIn('id', array_diff($old_attachmentModelIds, $new_attachmentModelIds))->delete();


    }




}
