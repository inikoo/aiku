<?php

namespace App\Models\Manufacturing;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Manufacturing\ArtifactManufactureTask
 *
 * @property int $id
 * @property int $artifact_id
 * @property int $manufacture_task_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Manufacturing\Artifact $artifact
 * @property-read \App\Models\Manufacturing\ManufactureTask $manufactureTask
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
