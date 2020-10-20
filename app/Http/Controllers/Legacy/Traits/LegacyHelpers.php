<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 16:15:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers\Legacy\Traits;


use Illuminate\Support\Arr;

trait LegacyHelpers {

    function parseRequest($request_data) {

        $data                  = Arr::pull($request_data, 'data', false);
        $settings              = Arr::pull($request_data, 'settings', false);
        $legacy                = Arr::pull($request_data, 'legacy', false);


        $this->data                  = ($data ? array_filter(json_decode($data, true)) : []);
        $this->settings              = ($settings ? array_filter(json_decode($settings, true)) : []);
        $this->legacy                = ($legacy ? array_filter(json_decode($legacy, true)) : []);


        $this->object_parameters = $request_data;

    }

    function commonUpdate($model) {

        $model->fill($this->object_parameters);
        $data = $this->data + $model->data;

        $data = array_filter($data);

        $model->data     = $data;
        $model->settings = $this->settings + $model->settings;
        $model->save();
        return $model;

    }

}
