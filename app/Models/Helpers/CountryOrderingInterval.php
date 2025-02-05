<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 05-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CountryOrderingInterval extends Model
{
    protected $table = 'country_ordering_intervals';

    protected $guarded = [];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
