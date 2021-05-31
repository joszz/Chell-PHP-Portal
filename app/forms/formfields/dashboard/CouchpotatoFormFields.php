<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

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
				'checked' => $form->settings->couchpotato->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->settings->couchpotato->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'couchpotato-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$couchpotatoAPIKey = new Password('couchpotato-apikey');
		$couchpotatoAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->couchpotato->api_key)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'couchpotato-enabled']));

		$rotateInterval = new Numeric('couchpotato-rotate-interval');
		$rotateInterval->setLabel('Rotate interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->couchpotato->rotate_interval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'couchpotato-enabled'])
			]);

        $tmdbAPIURL = new Text('couchpotato-tmdb-apiurl');
        $tmdbAPIURL->setLabel('TMDB API URL')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => true])
            ->setDefault($form->settings->couchpotato->tmdb_api_url)
            ->addValidator(new UrlValidator(['message' => $form->translator->validation['url']]));

        $tmdbAPIKey = new Password('couchpotato-tmdb-apikey');
        $tmdbAPIKey->setLabel('TMDB API key')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
            ->setDefault($form->settings->couchpotato->tmdb_api_key);

		$form->add($couchpotatoEnabled);
		$form->add($couchpotatoURL);
		$form->add($couchpotatoAPIKey);
		$form->add($rotateInterval);
        $form->add($tmdbAPIURL);
        $form->add($tmdbAPIKey);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$settings, $data)
    {
        $settings->couchpotato->enabled = isset($data['couchpotato-enabled']) && $data['couchpotato-enabled'] == 'on' ? '1' : '0';
        $settings->couchpotato->url = $data['couchpotato-url'];
        $settings->couchpotato->api_key = $data['couchpotato-apikey'];
        $settings->couchpotato->rotate_interval = $data['couchpotato-rotate-interval'];
        $settings->couchpotato->tmdb_api_url = $data['couchpotato-tmdb-apiurl'];
        $settings->couchpotato->tmdb_api_key = $data['couchpotato-tmdb-apikey'];
    }
}