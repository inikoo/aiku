<?php

namespace App\Models\Search;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class UniversalSearch extends Model
{
    use UsesTenantConnection;
    use Searchable;

    protected $guarded = [];
}
