<?php

namespace Chell\Forms\Validators;

use Phalcon\Validation\ValidatorInterface;
use Phalcon\Validation\Validator\Confirmation as ConfirmationValidator;
use Phalcon\Messages\Message;

class PresenceOfConfirmation extends ConfirmationValidator implements ValidatorInterface {

    public function validate(\Phalcon\Validation $validation, $field) : bool {

        $with = $this->getOption("with");
        $with_value = $validation->getValue($with);
        $value = $validation->getValue($field);

        if ($with_value == 'on' && empty($value))
        {
            $message = $this->getOption('message');
            $validation->appendMessage(new Message($message, $field, 'StrictConfirmation'));

            return false;
        }

        return true;
    }
}