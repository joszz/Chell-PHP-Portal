<?php

namespace Chell\Forms\FormFields\General;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;

class DuoFormFields extends FormFields
{
	protected function initializeFields()
	{
        $this->fields[] = $duoEnabled = new Check('duo-enabled');
        $duoEnabled->setLabel('Enabled');
        $duoEnabled->setAttributes([
            'value' => '1',
            'checked' => $this->form->settings->duo->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Duo'
        ]);

        $this->fields[] = $duoAPIHostname = new Text('duo-api_hostname');
        $duoAPIHostname->setLabel('API hostname')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => true])
            ->setDefault($this->form->settings->duo->api_hostname)
            ->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'duo-enabled']));

        $this->fields[] = $duoIKey = new Password('duo-ikey');
        $duoIKey->setLabel('Integration key')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                ->setDefault($this->form->settings->duo->ikey)
                ->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'duo-enabled']));

        $this->fields[] = $duoSKey = new Password('duo-skey');
        $duoSKey->setLabel('Secret key')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                ->setDefault($this->form->settings->duo->skey)
                ->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'duo-enabled']));

        $this->fields[] = $duoAKey = new Password('duo-akey');
        $duoAKey->setLabel('Akey')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
                ->setDefault($this->form->settings->duo->akey)
                ->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'duo-enabled']));
	}
}