<?php

namespace Chell\Forms\Validators;

use Phalcon\Messages\Message;
use Phalcon\Validation\Validator\Confirmation;

class PresenceOfConfirmation extends Confirmation
{
    public function validate(\Phalcon\Validation $validation, $field) : bool
    {
        $with = $this->getOption("with");
        $with_value = $validation->getValue($with);
        $value = $validation->getValue($field);

        if ($with_value == 'on' && empty($value))
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