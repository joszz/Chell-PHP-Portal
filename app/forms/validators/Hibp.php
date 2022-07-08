<?php

namespace Chell\Forms\Validators;

use Phalcon\Filter\Validation;
use Phalcon\Messages\Message;
use Phalcon\Filter\Validation\AbstractValidator;
use MFlor\Pwned\Pwned;

/**
 * The HaveIBeenPwned validator
 *
 * @package Formvalidators
 */
class Hibp extends AbstractValidator
{
    public function validate(Validation $validation, $field) : bool
    {
        $pwned = new Pwned();
        $value = $validation->getValue($field);
        $valid = true;

        if (!empty($value))
        {
            $passwordOccurences = $pwned->passwords()->occurrences($value);
            $valid = $passwordOccurences == 0;
        }

        if (!$valid)
        {
            $message = $this->getOption('message');

            if (!$message) {
                $message = 'The specified password is contained in the HIBP database';
            }

            $validation->appendMessage(new Message($message, $field, 'Hibp'));
        }

        return $valid;
    }
}