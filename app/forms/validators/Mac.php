<?php

namespace Chell\Forms\Validators;

use Phalcon\Messages\Message;
use Phalcon\Validation\AbstractValidator;

class Mac extends AbstractValidator
{
    public function validate(\Phalcon\Validation $validation, $field) : bool
    {
        $valid = filter_var($validation->getValue($field), FILTER_VALIDATE_MAC) !== false;

        if (!$valid)
        {
            $message = $this->getOption('message');

            if (!$message) {
                $message = 'The MAC address is not valid';
            }

            $validation->appendMessage(new Message($message, $field, 'Mac'));
        }

        return $valid;
    }
}