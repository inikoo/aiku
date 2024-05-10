<?php

namespace App\Models\Manufacturing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Manufacturing\ArtifactManufactureTask
 *

 * @property-read Artifact|null $artifact
 * @property-read ManufactureTask|null $manufactureTask
 * @method static \Illuminate\Database\Eloquent\Builder|ArtifactManufactureTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArtifactManufactureTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArtifactManufactureTask query()
 * @mixin \Eloquent
 */

class ArtifactManufactureTask extends Pivot
{
    protected $table = 'artifacts_manufacture_tasks';

    public $incrementing = true;

    protected $guarded = [];

    public function artifact(): BelongsTo
    {
        return $this->belongsTo(Artifact::class);
    }

    public function manufactureTask(): BelongsTo
    {
        return $this->belongsTo(ManufactureTask::class);
    }
}
