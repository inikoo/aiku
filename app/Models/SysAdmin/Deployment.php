<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 17:21:07 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;


/**
 * App\Models\SysAdmin\Deployment
 *
 * @property-read mixed $skip_build
 * @property-read mixed $skip_composer_install
 * @property-read mixed $skip_npm_install
 * @method static Builder|Deployment newModelQuery()
 * @method static Builder|Deployment newQuery()
 * @method static Builder|Deployment query()
 * @mixin \Eloquent
 */
class Deployment extends Model
{

    protected $guarded = [];
    protected $attributes = [
        'data' => '{}',
    ];
    protected $casts = [
        'data' => 'array'
    ];

    protected $appends = ['skip_build','skip_npm_install','skip_composer_install'];

    public function getSkipComposerInstallAttribute(){
        return Arr::get($this->data,'skip.composer_install',false);
    }
    public function getSkipNpmInstallAttribute(){
        return Arr::get($this->data,'skip.npm_install',false);
    }
    public function getSkipBuildAttribute(){
        return Arr::get($this->data,'skip.build',false);
    }

}
