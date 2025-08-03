<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CountryPhonePrefix implements ValidationRule
{
    protected string $countryCode;

    public function __construct(string $countryCode = '050')
    {
        $this->countryCode = $countryCode;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $countries = json_decode(file_get_contents(public_path('countries.json')), true);
        $country = $countries[$this->countryCode];
        // remove "+"
        $value = str_replace('+',"",$value);


        if (!isset($country)) {
            $fail("Invalid country code.");
            return;
        }
        $prefix = $country['prefix'];

        if (!str_starts_with($value, $prefix)) {
            $fail($country['name'] . " phone numbers must start with +"  .  $country['prefix'] );
            return;
        }

        // check 8 numbers is followed 
        $number_without_prefix = str_replace($country['prefix'],"",$value);
        if(strlen($number_without_prefix) != 8){
            $fail("Phone must be country code followed by 8 numbers");
        }
    }
}
