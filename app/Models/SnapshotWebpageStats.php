<?php

namespace App\Models;

use App\Models\Web\Webpage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
*
 * @property int $webpage_id
 * @property int $number_web_blocks
 * @property int $number_menu_columns
 * @property int $number_menu_items
 * @property int $number_columns
 * @property int $number_header_columns
 * @property int $number_footer_columns
 * @property int $height_desktop
 * @property int $height_mobile
 * @property int $number_internal_links
 * @property int $number_external_links
 * @property int $number_images
 * @property int $filesize
 */
class SnapshotWebpageStats extends Model
{
    protected $guarded = [];

    public function webpage(): BelongsTo
    {
        return $this->belongsTo(Webpage::class);
    }
}
