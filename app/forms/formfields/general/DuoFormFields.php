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
            'checked' => $form->config->duo->enabled == '1' ? 'checked' : null,
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
                       ->setDefault($form->config->duo->apiHostname)
                       ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'duo-enabled']));

        $duoIKey = new Password('duo-ikey');
        $duoIKey->setLabel('Integration key')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                ->setDefault($form->config->duo->ikey)
                ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'duo-enabled']));

        $duoSKey = new Password('duo-skey');
        $duoSKey->setLabel('Secret key')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                ->setDefault($form->config->duo->skey)
                ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'duo-enabled']));

        $duoAKey = new Password('duo-akey');
        $duoAKey->setLabel('Akey')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
                ->setDefault($form->config->duo->akey)
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
    public function setPostData(&$config, $data)
    {
        $config->duo->enabled = isset($data['duo-enabled']) && $data['duo-enabled'] == 'on' ? '1' : '0';
        $config->duo->apiHostname = $data['duo-apiHostname'];
        $config->duo->ikey = $data['duo-ikey'];
        $config->duo->skey = $data['duo-skey'];
        $config->duo->akey = $data['duo-akey'];
    }
}