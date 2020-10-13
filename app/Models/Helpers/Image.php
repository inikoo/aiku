<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 12 Oct 2020 23:05:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 *
 * @property int $id
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Image extends Model {
    use UsesTenantConnection;


    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function models() {
        return $this->hasMany('App\Models\Helpers\ImageModel');
    }

    public function save_image($image_filename_data) {

        $imagePath = $image_filename_data['image_path'];

        $size_data = getimagesize($imagePath);
        $width     = $size_data[0];
        $height    = $size_data[0];


        $data = [
            'mime'     => Arr::get($size_data, 'mime', $image_filename_data['mime']),
            'bits'     => Arr::get($size_data, 'bits'),
            'channels' => Arr::get($size_data, 'channels'),
            'width'    => $width,
            'height'   => $height,
        ];
        $data = array_filter($data);

        $originalImage = (new OriginalImage)->firstOrCreate(
            ['checksum' => md5_file($imagePath)], [
                                                    'filesize'   => filesize($imagePath),
                                                    'megapixels' => $width * $height / 1000000,
                                                    'image_data' => pg_escape_bytea(file_get_contents($imagePath)),
                                                    'data'       => $data
                                                ]
        );


        if (!$originalImage->communal_image) {


            $originalImage->communal_image()->save(new CommunalImage());

        }

        $this->communal_image_id =  (new OriginalImage)->find($originalImage->id)->communal_image->id;


        $this->checksum          = $originalImage->checksum;
        $this->save();


    }

}
