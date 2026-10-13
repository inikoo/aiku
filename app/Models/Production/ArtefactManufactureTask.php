<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

/**
 * App\Models\Production\ArtefactManufactureTask
 *
 * @property int $id
 * @property int $artefact_id
 * @property int $manufacture_task_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $position
 * @property numeric $units_per_artefact
 * @property numeric|null $standard_rate
 * @property-read \App\Models\Production\Artefact|null $artefact
 * @property-read \App\Models\Production\ManufactureTask|null $manufactureTask
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Production\RecipeStepRawMaterial> $rawMaterials
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtefactManufactureTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtefactManufactureTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtefactManufactureTask query()
 * @mixin \Eloquent
 */

class ArtefactManufactureTask extends Pivot
{
    protected $table = 'artefacts_manufacture_tasks';

    public $incrementing = true;

    protected $guarded = [];

    /**
     * How many artefacts one unit of the step holds, when the step is counted in packs (a tray of 8
     * has units_per_artefact 0.125); null when the step is counted in artefacts or parts of one.
     */
    public static function packSize(float|string|null $unitsPerArtefact): ?int
    {
        $unitsPerArtefact = (float) $unitsPerArtefact;

        return $unitsPerArtefact > 0 && $unitsPerArtefact < 1 ? (int) round(1 / $unitsPerArtefact) : null;
    }

    /**
     * @return Collection<string, int> pack size by "artefact id:manufacture task id", only for steps counted in packs
     */
    public static function packSizes(): Collection
    {
        return self::where('units_per_artefact', '<', 1)
            ->get(['artefact_id', 'manufacture_task_id', 'units_per_artefact'])
            ->mapWithKeys(fn (self $step) => [$step->artefact_id.':'.$step->manufacture_task_id => self::packSize($step->units_per_artefact)]);
    }

    public function artefact(): BelongsTo
    {
        return $this->belongsTo(Artefact::class);
    }

    public function manufactureTask(): BelongsTo
    {
        return $this->belongsTo(ManufactureTask::class);
    }

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RecipeStepRawMaterial::class, 'artefact_manufacture_task_id');
    }
}
