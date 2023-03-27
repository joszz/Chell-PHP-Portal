<?php

namespace Chell\Forms\FormFields\General;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;

/**
 * The formfields for the Apache plugin
 *
 * @package Formfields
 */
class DuoFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
        $this->hasFieldset = true;

        $this->fields[] = new Check('duo-enabled', [
            'checked' => $this->form->settings->duo->enabled->value == '1' ? 'checked' : null,
            'fieldset' => 'Duo'
        ]);

        $this->fields[] = $duoAPIHostname = new Text('duo-api_hostname');
        $duoAPIHostname->setLabel('API hostname')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->form->settings->duo->api_hostname->value)
            ->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'duo-enabled']));

        $this->fields[] = $duoIKey = new Password('duo-clientid');
        $duoIKey->setLabel('Client Id')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control'])
                ->setDefault($this->form->settings->duo->clientid->value)
                ->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'duo-enabled']));

        $this->fields[] = $duoSKey = new Password('duo-clientsecret');
        $duoSKey->setLabel('Client secret')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control'])
                ->setDefault($this->form->settings->duo->clientsecret->value)
                ->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'duo-enabled']));
	}
}