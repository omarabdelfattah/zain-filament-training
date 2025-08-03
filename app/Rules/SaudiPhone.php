<?php
namespace App\Rules;


use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaudiPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^050[0-9]{8}$/', $value)) {
            $fail('should be saudi number starts with prefix  "050" then followed by 8 numbers');
        }
    }
}
