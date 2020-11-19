<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 20 Nov 2020 00:53:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Helpers\Image;
use App\Models\Helpers\ImageModel;
use Illuminate\Support\Facades\DB;

function get_image_filename_legacy($tenant, $image_legacy_data) {


    $image_path = sprintf(config('app.legacy.images_path'), $tenant->data['legacy']['code']).$image_legacy_data->{'Image File Checksum'}[0].'/'.$image_legacy_data->{'Image File Checksum'}[1].'/'.$image_legacy_data->{'Image File Checksum'}.'.'
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
            //
        }
    }

    return $newImageModelId;

}

function get_images_data($tenant, $params) {

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
        $image_filename_data = get_image_filename_legacy($tenant, $image_legacy_data);
        if ($image_filename_data) {


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
                    'tenant_id'  => $tenant->id,
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
