<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 17:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Production;

use App\Actions\Helpers\Translations\Translate;
use App\Models\Helpers\Language;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Why an artisan needed Preparation time, chosen on the tablet when they finish it. The Production Manager edits the list;
 * "Other" always stays, and asks the artisan to type the reason.
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $production_id
 * @property string $name
 * @property bool $is_other
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Production $production
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManufacturePreparationReason newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManufacturePreparationReason newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManufacturePreparationReason query()
 * @mixin \Eloquent
 */
class ManufacturePreparationReason extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_other' => 'boolean',
    ];

    public const array DEFAULT_REASONS = [
        'Bringing materials from the production location (bottles, caps, boxes)',
        'Cutting rope',
        'Setting up the workstation or equipment',
        'Waiting for materials or instructions',
    ];

    /**
     * The production's reasons in order, "Other" last; the defaults are set up the first time a production needs them.
     *
     * @return Collection<int, self>
     */
    public static function forProduction(Production $production): Collection
    {
        if (!self::withTrashed()->where('production_id', $production->id)->exists()) {
            DB::transaction(function () use ($production) {
                Production::whereKey($production->id)->lockForUpdate()->first();
                if (self::withTrashed()->where('production_id', $production->id)->exists()) {
                    return;
                }
                foreach ([...self::DEFAULT_REASONS, 'Other'] as $position => $name) {
                    self::create([
                        'group_id'        => $production->group_id,
                        'organisation_id' => $production->organisation_id,
                        'production_id'   => $production->id,
                        'name'            => $name,
                        'is_other'        => $name === 'Other',
                        'position'        => $position,
                    ]);
                }
            });
        }

        return self::where('production_id', $production->id)->orderBy('is_other')->orderBy('position')->orderBy('id')->get();
    }

    /**
     * The name in the tablet's language. Reasons are saved and reported in English, so this is only the button label.
     * ponytail: translated on render and kept by Translate's cache (60 days for short text); store the translations on the row if a cache miss ever slows the tablet.
     */
    public function nameIn(?Language $language): string
    {
        $english = Language::where('code', 'en')->first();
        if (!$language || !$english || $language->id == $english->id) {
            return $this->name;
        }

        return Translate::run($this->name, $english, $language);
    }

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }
}
