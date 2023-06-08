<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 19:17:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Media;

use App\Models\Traits\UsesGroupConnection;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * App\Models\GroupMedia\GroupMedia
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string|null $uuid
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string|null $mime_type
 * @property string $disk
 * @property string|null $conversions_disk
 * @property int $size
 * @property array $manipulations
 * @property array $custom_properties
 * @property array $generated_conversions
 * @property array $responsive_images
 * @property int|null $order_column
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|\Eloquent $model
 * @method static MediaCollection<int, static> all($columns = ['*'])
 * @method static MediaCollection<int, static> get($columns = ['*'])
 * @method static Builder|GroupMedia newModelQuery()
 * @method static Builder|GroupMedia newQuery()
 * @method static Builder|Media ordered()
 * @method static Builder|GroupMedia query()
 * @mixin Eloquent
 */
class GroupMedia extends BaseMedia
{
    use UsesGroupConnection;
    protected $table = 'group_media';
}
