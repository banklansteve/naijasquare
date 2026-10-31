<?php

namespace App\Rules;

use App\Signup\UkMobile;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UkMobileNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || UkMobile::normalise($value) === null) {
            $fail('Enter a UK mobile number, or leave this blank.');
        }
    }
}
