<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 29 Sep 2020 17:21:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands\Traits;

use App\Models\Helpers\Address;
use App\Models\Helpers\Attachment;
use App\Models\Helpers\AttachmentModel;
use App\Models\Helpers\Image;
use App\Models\Helpers\ImageModel;
use App\Models\Sales\Charge;
use App\Models\Sales\ShippingZone;
use App\Models\Sales\TaxBand;
use Exception;
use Illuminate\Support\Arr;
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




    function elementsToLower($elements_keys, $array) {

        foreach ($elements_keys as $key) {
            Arr::set(
                $array, $key, strtolower(Arr::get($array, $key))
            );
        }

        return $array;

    }

    function get_instance_address_scaffolding($object, $type, $legacy_data) {

       if($object=='CustomerClient'){
           $legacy_object='Customer Client';
       }else{
           $legacy_object= $object;
       }


        if ($type != '') {
            $type = ' '.$type;
        }

        $_address                      = new Address();
        $_address->address_line_1      = $legacy_data->{$legacy_object.$type.' Address Line 1'};
        $_address->address_line_2      = $legacy_data->{$legacy_object.$type.' Address Line 2'};
        $_address->sorting_code        = $legacy_data->{$legacy_object.$type.' Address Sorting Code'};
        $_address->postal_code         = $legacy_data->{$legacy_object.$type.' Address Postal Code'};
        $_address->locality            = $legacy_data->{$legacy_object.$type.' Address Locality'};
        $_address->dependent_locality  = $legacy_data->{$legacy_object.$type.' Address Dependent Locality'};
        $_address->administrative_area = $legacy_data->{$legacy_object.$type.' Address Administrative Area'};
        $_address->country_code        = $legacy_data->{$legacy_object.$type.' Address Country 2 Alpha Code'};

        return $_address;


    }


    function process_instance_address($object, $object_key, $type, $legacy_data) {


        $_address = $this->get_instance_address_scaffolding($object, $type, $legacy_data);

        return (new Address)->firstOrCreate(
            [
                'checksum'   => $_address->getChecksum(),
                'owner_type' => $object,
                'owner_id'   => $object_key,

            ], [
                'address_line_1'      => $_address->address_line_1,
                'address_line_2'      => $_address->address_line_2,
                'sorting_code'        => $_address->sorting_code,
                'postal_code'         => $_address->postal_code,
                'locality'            => $_address->locality,
                'dependent_locality'  => $_address->dependent_locality,
                'administrative_area' => $_address->administrative_area,
                'country_code'        => $_address->country_code,

            ]
        );


    }

    function process_immutable_address($object, $type, $legacy_data) {


        $_address = $this->get_instance_address_scaffolding($object, $type, $legacy_data);

        return (new Address)->firstOrCreate(
            [
                'checksum'   => $_address->getChecksum(),
                'owner_type' => null,
                'owner_id'   => null,

            ], [
                'address_line_1'      => $_address->address_line_1,
                'address_line_2'      => $_address->address_line_2,
                'sorting_code'        => $_address->sorting_code,
                'postal_code'         => $_address->postal_code,
                'locality'            => $_address->locality,
                'dependent_locality'  => $_address->dependent_locality,
                'administrative_area' => $_address->administrative_area,
                'country_code'        => $_address->country_code,

            ]
        );


    }


    function get_image_filename($image_legacy_data) {


        $image_path = sprintf(config('legacy.images_path'), $this->tenant->data['legacy']['code']).$image_legacy_data->{'Image File Checksum'}[0].'/'.$image_legacy_data->{'Image File Checksum'}[1].'/'.$image_legacy_data->{'Image File Checksum'}.'.'
            .$image_legacy_data->{'Image File Format'};


        if (file_exists($image_path)) {
            return [
                'image_path' => $image_path,
                'filename'   => $image_legacy_data->{'Image Filename'},
                'mime'       => $image_legacy_data->{'Image MIME Type'}
            ];
        } else {
            return false;
        }

    }


    function get_images_data($params) {

        $image_table    = '`Image Subject Bridge` B ';
        $imageModelData = [];

        $limit = '';
        if (!empty($params['limit'])) {
            $limit = ' limit '.$params['limit'];
        }

        foreach (
            DB::connection('legacy')->select(
                "select * from $image_table left join  `Image Dimension` I on (`Image Subject Image Key`=`Image Key`)  where   `Image Subject Object`=?  and `Image Subject Object Key`=?  ORDER BY FIELD(`Image Subject Is Principal`, 'Yes','No') $limit",

                [
                    $params['object'],
                    $params['object_key']
                ]
            ) as $image_legacy_data
        ) {

            if ($image_filename_data = $this->get_image_filename($image_legacy_data)) {


                $image_data = fill_legacy_data(
                    [
                        'mime_type' => 'Image MIME Type',
                        'width'     => 'Image Width',
                        'height'    => 'Image Height',


                    ], $image_legacy_data
                );


                $image = (new Image)->updateOrCreate(
                    [
                        'legacy_id' => $image_legacy_data->{'Image Key'}
                    ], [
                        'tenant_id' => $this->tenant->id,

                        'created_at' => $image_legacy_data->{'Image Creation Date'},
                        'data'       => $image_data
                    ]
                );

                if (!$image->communal_image_id) {
                    $image->save_image($image_filename_data);

                }
                $imageModel = new ImageModel();

                $imageModel->data = [

                ];


                $imageModelData[] = [
                    'image_id' => $image->id,
                    'scope'    => $image_legacy_data->{'Image Subject Object Image Scope'},
                    'data'     => [
                        'filename' => $image_filename_data['filename']
                    ]
                ];

            }

        }

        return $imageModelData;

    }

    function sync_images($model, $imagesModelData, $get_scope) {

        $old_imageModelIds = [];
        $new_imageModelIds = [];

        $model->images()->get()->each(
            function ($imageModel) use (&$old_imageModelIds) {
                $old_imageModelIds[] = $imageModel->id;
            }
        );
        $precedence = 1;
        foreach ($imagesModelData as $imageModelData) {


            $scope = $get_scope($imageModelData['scope']);

            $imageModel          = (new ImageModel)->updateOrCreate(
                [
                    'imageable_type' => $model->getMorphClass(),
                    'imageable_id'   => $model->id,
                    'scope'          => $scope,
                    'image_id'       => $imageModelData['image_id'],

                ], [
                    'data'       => $imageModelData['data'],
                    'precedence' => $precedence
                ]
            );
            $new_imageModelIds[] = $imageModel->id;
            $model->images()->save($imageModel);
            $precedence--;

        }
        $model->images()->whereIn('id', array_diff($old_imageModelIds, $new_imageModelIds))->delete();


    }

    function sync_image($model, $imagesModelData, $get_scope) {

        $oldImageModelId = null;
        $newImageModelId = null;

        if ($model->image) {
            $oldImageModelId = $model->image->id;

        }


        foreach ($imagesModelData as $imageModelData) {


            $scope = $get_scope($imageModelData['scope']);

            $imageModel      = (new ImageModel)->updateOrCreate(
                [
                    'imageable_type' => $model->getMorphClass(),
                    'imageable_id'   => $model->id,
                    'scope'          => $scope,

                ], [
                    'image_id' => $imageModelData['image_id'],
                    'data'     => $imageModelData['data']
                ]
            );
            $newImageModelId = $imageModel->id;
            $model->image()->save($imageModel);
            break;

        }
        if ($oldImageModelId and $oldImageModelId != $newImageModelId) {
            try {
                (new ImageModel)->find($oldImageModelId)->delete();
            } catch (Exception $e) {
            }
        }

        return $newImageModelId;

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

    function get_transaction_data($onptf_data) {

        switch ($onptf_data->{'Transaction Type'}) {
            case 'Shipping':
                $transaction_type = 'ShippingZone';
                $transaction_id   = null;
                if ($onptf_data->{'Transaction Type Key'}) {
                    if ($shipping_zone = (new ShippingZone())->firstWhere('legacy_id', $onptf_data->{'Transaction Type Key'})) {
                        $transaction_id = $shipping_zone->id;
                    }

                }
                break;
            case 'Charges':
                $transaction_type = 'Charge';
                $transaction_id   = null;
                if ($onptf_data->{'Transaction Type Key'}) {
                    if ($charge = (new Charge())->firstWhere('legacy_id', $onptf_data->{'Transaction Type Key'})) {
                        $transaction_id = $charge->id;
                    }

                }
                break;
            default:
                print_r($onptf_data);
                exit();
        }
        $tax_band_id = null;
        if ($taxBand = (new TaxBand)->firstwhere('code', strtolower($onptf_data->{'Tax Category Code'}))) {
            $tax_band_id = $taxBand->id;
        } else {
            print_r($onptf_data);
            exit;
        }


        return [
            'type'        => $transaction_type,
            'id'          => $transaction_id,
            'tax_band_id' => $tax_band_id

        ];
    }


}
