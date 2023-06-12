<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ValidAddress implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query         = DB::table('countries');
        if ($query->where("id", $value['country_id'])->count() <= 0) {
            $fail('The '.$attribute.' not a valid Address.');
        }
    }
}
