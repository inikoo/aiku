<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class PinRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    protected $organisationId;

    public function __construct($organisationId)
    {
        $this->organisationId = $organisationId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'X', 'Y', 'Z');
        $emojis  = array('🌴', '😀', '👽', '🍄', '👻', '👍🏼', '🚀', '🦄', '🐋', '☘️');
        $numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

        $letterCount = 0;
        $emojiCount  = 0;
        $numberCount = 0;

        $characters = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($characters as $char) {
            if (in_array($char, $letters)) {
                $letterCount++;
            }
            if (in_array($char, $emojis)) {
                $emojiCount++;
            }
            if (in_array($char, $numbers)) {
                $numberCount++;
            }
        }

        if ($letterCount < 2 || $emojiCount < 2 || $numberCount < 2) {
            $fail(__('validation.pin', ['attribute' => $attribute]));
        }

        $exists = DB::table('employees')
        ->where('organisation_id', $this->organisationId)
        ->where('pin', $value)
        ->exists();

        if ($exists) {
            $fail(__('validation.unique_pin', ['attribute' => $attribute]));
        }
    }
}
