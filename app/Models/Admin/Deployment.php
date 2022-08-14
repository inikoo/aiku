<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 14 Aug 2022 22:44:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;


/**
 * App\Models\Admin\Deployment
 *
 * @property int $id
 * @property string $version
 * @property string $hash
 * @property string $state
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $skip_build
 * @property-read mixed $skip_composer_install
 * @property-read mixed $skip_npm_install
 * @method static Builder|Deployment newModelQuery()
 * @method static Builder|Deployment newQuery()
 * @method static Builder|Deployment query()
 * @method static Builder|Deployment whereCreatedAt($value)
 * @method static Builder|Deployment whereData($value)
 * @method static Builder|Deployment whereHash($value)
 * @method static Builder|Deployment whereId($value)
 * @method static Builder|Deployment whereState($value)
 * @method static Builder|Deployment whereUpdatedAt($value)
 * @method static Builder|Deployment whereVersion($value)
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
