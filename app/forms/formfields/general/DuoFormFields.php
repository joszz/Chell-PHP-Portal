<?php

namespace Chell\Forms\FormFields\General;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;

class DuoFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
        $duoEnabled = new Check('duo-enabled');
        $duoEnabled->setLabel('Enabled');
        $duoEnabled->setAttributes([
            'checked' => $form->settings->duo->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Duo'
        ]);

        $duoAPIHostname = new Text('duo-apiHostname');
        $duoAPIHostname->setLabel('API hostname')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => true])
            ->setDefault($form->settings->duo->api_hostname)
            ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'duo-enabled']));

        $duoIKey = new Password('duo-ikey');
        $duoIKey->setLabel('Integration key')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                ->setDefault($form->settings->duo->ikey)
                ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'duo-enabled']));

        $duoSKey = new Password('duo-skey');
        $duoSKey->setLabel('Secret key')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                ->setDefault($form->settings->duo->skey)
                ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'duo-enabled']));

        $duoAKey = new Password('duo-akey');
        $duoAKey->setLabel('Akey')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
                ->setDefault($form->settings->duo->akey)
                ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'duo-enabled']));

        $form->add($duoEnabled);
        $form->add($duoAPIHostname);
        $form->add($duoIKey);
        $form->add($duoSKey);
        $form->add($duoAKey);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$settings, $data)
    {
        $settings->duo->enabled = isset($data['duo-enabled']) && $data['duo-enabled'] == 'on' ? '1' : '0';
        $settings->duo->api_hostname = $data['duo-apiHostname'];
        $settings->duo->ikey = $data['duo-ikey'];
        $settings->duo->skey = $data['duo-skey'];
        $settings->duo->akey = $data['duo-akey'];
    }
}