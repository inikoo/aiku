<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 28 Oct 2021 22:30:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions;

use Illuminate\Support\Arr;

trait WithUpdate{

    protected function extractJson($modelData,$field=['data']): array
    {
        $data=[];
        foreach(Arr::dot(Arr::only($modelData, $field)) as $key=>$value){
            if(is_array($value)){
                if(count($value)==0){
                    $value=null;
                }else{
                    $value=json_encode($value);

                }
            }
            if($value){
                $data[preg_replace('/\./','->',$key)]=$value;
            }
        }
        return $data;
    }

}

