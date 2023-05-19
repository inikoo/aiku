<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CaseSensitive implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public  $tableName, $message;
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $query = DB::connection('tenant')->table($this->tableName);
        $column = $query->getGrammar()->wrap($attribute);
        $this->message = $value . ' already exist table';
        return ! $query->whereRaw("lower({$column}) = lower(?)", [$value])->count();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }
}
