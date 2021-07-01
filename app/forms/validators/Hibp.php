<?php

namespace Chell\Forms\Validators;

use Phalcon\Validation;
use Phalcon\Messages\Message;
use Phalcon\Validation\AbstractValidator;
use MFlor\Pwned\Pwned;

class Hibp extends AbstractValidator
{
    public function validate(Validation $validation, $field) : bool
    {
        $pwned = new Pwned();
        $passwordOccurences = $pwned->passwords()->occurrences($validation->getValue($field));
        $valid = $passwordOccurences == 0;

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