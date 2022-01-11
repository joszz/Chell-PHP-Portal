<?php

namespace Chell\Forms;

use Chell\Models\Users;
use Chell\Forms\Validators\Hibp;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Email;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Confirmation;
use Phalcon\Filter\Validation\Validator\Email as EmailValidator;

/**
 * The form responsible for updating existing Users.
 *
 * @package Forms
 */
class SettingsUserForm extends SettingsBaseForm
{
	/**
     * Add all fields to the form and set form specific attributes.
     */
    public function initialize(Users $user)
    {
        $username = new Text('username');
        $username->setLabel('Username')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'autocomplete' => 'off'])
            ->addValidator(new PresenceOf(['message' => $this->translator->validation['required']]));

        if (isset($user->username))
        {
            $username->setDefault($user->username);
        }

        $email = new Email('email');
        $email->setLabel('Email')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'autocomplete' => 'off'])
            ->addValidator(new EmailValidator(['message' => $this->translator->validation['email']]));

        $password = new Password('password');
        $password->setLabel('Password')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password'])
            ->addValidators([
                new PresenceOf(['message' => $this->translator->validation['required']]),
                new Confirmation(['message' => $this->translator->validation['password-not-match'], 'with' => 'password_again'])
            ]);

        $passwordAgain = new Password('password_again');
        $passwordAgain->setLabel('Password again')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password'])
            ->addValidators([
                new PresenceOf(['message' => $this->translator->validation['required']]),
                new Confirmation(['message' => $this->translator->validation['password-not-match'], 'with' => 'password_again'])
            ]);

        if ($this->settings->hibp->enabled)
        {
            $password->addValidator(new Hibp(['message' => $this->translator->validation['hibp']]));
            $passwordAgain->addValidator(new Hibp(['message' => $this->translator->validation['hibp']]));
        }

        $this->add($username);
        $this->add($email);
        $this->add($password);
        $this->add($passwordAgain);
    }
}