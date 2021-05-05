<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Url as UrlValidator;

class PiholeFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$piholeEnabled = new Check('pihole-enabled');
		$piholeEnabled->setLabel('Enabled');
		$piholeEnabled->setAttributes([
			'checked' => $form->config->pihole->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Pi-hole'
		]);

		$piholeURL = new Text('pihole-url');
		$piholeURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->config->pihole->URL)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'pihole-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$form->add($piholeEnabled);
		$form->add($piholeURL);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->pihole->enabled = isset($data['pihole-enabled']) && $data['pihole-enabled'] == 'on' ? '1' : '0';
        $config->pihole->URL = $data['pihole-url'];
    }
}