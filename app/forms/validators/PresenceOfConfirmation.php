<?php

namespace Chell\Forms\Validators;

use Phalcon\Validation;
use Phalcon\Messages\Message;
use Phalcon\Validation\Validator\Confirmation;

class PresenceOfConfirmation extends Confirmation
{
    public function validate(Validation $validation, $field) : bool
    {
        $with = $this->getOption("with");
        $with_value = $validation->getValue($with);
        $value = $validation->getValue($field);

        if ($with_value == '1' && empty($value))
        {
            $message = $this->getOption('message');

            if (!$message)
            {
                $message = 'Required';
            }

            $validation->appendMessage(new Message($message, $field, 'PresenceOfConfirmation'));
            return false;
        }

        return true;
    }
}