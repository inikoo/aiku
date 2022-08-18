<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 17 Aug 2022 13:17:23 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Organisations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Organisations\UserLinkCode
 *
 * @property int $id
 * @property int $organisation_id
 * @property string $code
 * @property string $organisation_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Organisations\Organisation|null $organisation
 * @method static Builder|UserLinkCode newModelQuery()
 * @method static Builder|UserLinkCode newQuery()
 * @method static Builder|UserLinkCode query()
 * @method static Builder|UserLinkCode whereCode($value)
 * @method static Builder|UserLinkCode whereCreatedAt($value)
 * @method static Builder|UserLinkCode whereDeletedAt($value)
 * @method static Builder|UserLinkCode whereId($value)
 * @method static Builder|UserLinkCode whereOrganisationId($value)
 * @method static Builder|UserLinkCode whereOrganisationUserId($value)
 * @method static Builder|UserLinkCode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserLinkCode extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'organisation_user_id',
    ];



    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

}
