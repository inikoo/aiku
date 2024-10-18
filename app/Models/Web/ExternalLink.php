<?php
/*
 * author Arya Permana - Kirin
 * created on 18-10-2024-16h-33m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $url
 * @property int $number_websites_shown
 * @property int $number_webpages_shown
 * @property int $number_web_blocks_shown
 * @property int $number_websites_hidden
 * @property int $number_webpages_hidden
 * @property int $number_web_blocks_hidden
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExternalLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExternalLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExternalLink query()
 * @mixin \Eloquent
 */
class ExternalLink extends Model
{
    protected $casts = [
    ];

    protected $attributes = [
    ];

    protected $guarded = [];

}
