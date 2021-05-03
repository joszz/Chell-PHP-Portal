<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class CouchpotatoFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$couchpotatoEnabled = new Check('couchpotato-enabled');
		$couchpotatoEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->config->couchpotato->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Couchpotato'
		]);

		$couchpotatoURL = new Text('couchpotato-url');
		$couchpotatoURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->couchpotato->URL)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'couchpotato-enabled']));

		$couchpotatoAPIKey = new Password('couchpotato-apikey');
		$couchpotatoAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->couchpotato->APIKey)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'couchpotato-enabled']));

		$rotateInterval = new Numeric('couchpotato-rotate-interval');
		$rotateInterval->setLabel('Rotate interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->config->couchpotato->rotateInterval)
			->addValidator(new Numericality(['message' => $form->translator->validation['not-a-number']]))
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'couchpotato-enabled']));

		$form->add($couchpotatoEnabled);
		$form->add($couchpotatoURL);
		$form->add($couchpotatoAPIKey);
		$form->add($rotateInterval);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->couchpotato->enabled = isset($data['couchpotato-enabled']) && $data['couchpotato-enabled'] == 'on' ? '1' : '0';
        $config->couchpotato->URL = $data['couchpotato-url'];
        $config->couchpotato->APIKey = $data['couchpotato-apikey'];
        $config->couchpotato->rotateInterval = $data['couchpotato-rotate-interval'];
    }
}