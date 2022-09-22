<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 12:39:40 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Stancl\Tenancy\Database\Concerns\CentralConnection;


/**
 * App\Models\Central\Deployment
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
    use CentralConnection;

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
