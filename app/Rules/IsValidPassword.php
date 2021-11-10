<?php

namespace App\Rules;

use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Rule;

class IsValidPassword implements Rule
{
    /**
     * Determine if the Uppercase Validation Rule passes.
     *
     * @var boolean
     */
    public $uppercasePasses = true;

    /**
     * Determine if the Numeric Validation Rule passes.
     *
     * @var boolean
     */
    public $numericPasses = true;

    /**
     * Determine if the Special Character Validation Rule passes.
     *
     * @var boolean
     */
    public $specialCharacterPasses = true;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->uppercasePasses = (Str::lower($value) !== $value);
        $this->numericPasses = ((bool)preg_match('/[0-9]/', $value));
        $this->specialCharacterPasses = ((bool)preg_match('/[^A-Za-z0-9]/', $value));

        return ($this->uppercasePasses && $this->numericPasses && $this->specialCharacterPasses);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        switch (true) {
            case !$this->uppercasePasses
                && $this->numericPasses
                && $this->specialCharacterPasses:
                return 'password must contain at least 1 uppercase character.';

            case $this->uppercasePasses
                && !$this->numericPasses
                && $this->specialCharacterPasses:
                return 'password must contain at least 1 number.';

            case $this->uppercasePasses
                && $this->numericPasses
                && !$this->specialCharacterPasses:
                return 'password must contain at least 1 special character.';

            case !$this->uppercasePasses
                && !$this->numericPasses
                && $this->specialCharacterPasses:
                return 'password must contain at least 1 uppercase character and 1 number.';

            case $this->uppercasePasses
                && !$this->numericPasses
                && !$this->specialCharacterPasses:
                return 'password must contain at least 1 number and 1 special character.';

            case !$this->uppercasePasses
                && $this->numericPasses
                && !$this->specialCharacterPasses:
                return 'password must contain at least 1 uppercase character and 1 special character.';

            case !$this->uppercasePasses
                && !$this->numericPasses
                && !$this->specialCharacterPasses:
                return 'password must be at least 8 characters and contain at least 1 uppercase character, 1 number, and 1 special character.';

            default:
                return 'password input invalid';
        }
    }
}