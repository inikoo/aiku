<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:29:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Marketing;

use App\Models\CRM\Customer;
use App\Models\Helpers\Address;
use App\Models\Organisations\Organisation;
use App\Models\Traits\HasAddress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\Marketing\Shop
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $company_name
 * @property string|null $contact_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $tax_number
 * @property string|null $tax_number_status
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property int|null $address_id
 * @property mixed $location
 * @property string $state
 * @property string $type
 * @property string|null $subtype
 * @property string|null $open_at
 * @property string|null $closed_at
 * @property int $language_id
 * @property int $currency_id
 * @property int $timezone_id
 * @property mixed $data
 * @property mixed $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static Builder|Shop newModelQuery()
 * @method static Builder|Shop newQuery()
 * @method static Builder|Shop query()
 * @method static Builder|Shop whereAddressId($value)
 * @method static Builder|Shop whereAuroraId($value)
 * @method static Builder|Shop whereClosedAt($value)
 * @method static Builder|Shop whereCode($value)
 * @method static Builder|Shop whereCompanyName($value)
 * @method static Builder|Shop whereContactName($value)
 * @method static Builder|Shop whereCreatedAt($value)
 * @method static Builder|Shop whereCurrencyId($value)
 * @method static Builder|Shop whereData($value)
 * @method static Builder|Shop whereDeletedAt($value)
 * @method static Builder|Shop whereEmail($value)
 * @method static Builder|Shop whereId($value)
 * @method static Builder|Shop whereIdentityDocumentNumber($value)
 * @method static Builder|Shop whereIdentityDocumentType($value)
 * @method static Builder|Shop whereLanguageId($value)
 * @method static Builder|Shop whereLocation($value)
 * @method static Builder|Shop whereName($value)
 * @method static Builder|Shop whereOpenAt($value)
 * @method static Builder|Shop wherePhone($value)
 * @method static Builder|Shop whereSettings($value)
 * @method static Builder|Shop whereState($value)
 * @method static Builder|Shop whereSubtype($value)
 * @method static Builder|Shop whereTaxNumber($value)
 * @method static Builder|Shop whereTaxNumberStatus($value)
 * @method static Builder|Shop whereTimezoneId($value)
 * @method static Builder|Shop whereType($value)
 * @method static Builder|Shop whereUpdatedAt($value)
 * @method static Builder|Shop whereWebsite($value)
 * @mixin \Eloquent
 * @property int|null $organisation_source_id
 * @method static Builder|Shop whereOrganisationSourceId($value)
 * @property int $organisation_id
 * @property-read \App\Models\Helpers\Address|null $address
 * @property-read string $formatted_address
 * @property-read Organisation|null $organisation
 * @property-read \App\Models\Marketing\ShopStats|null $stats
 * @method static Builder|Shop whereOrganisationId($value)
 */
class Shop extends Model
{
    use HasAddress;
    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'location' => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
        'location' => '{}',
    ];

    protected $guarded = [];

    public function stats(): HasOne
    {
        return $this->hasOne(ShopStats::class);
    }

    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'addressable')->withTimestamps();
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }


}
