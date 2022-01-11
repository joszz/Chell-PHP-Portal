<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Numericality;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

class CouchpotatoFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = new Check('couchpotato-enabled', [
			'fieldset' => 'Couchpotato',
			'checked' => $this->form->settings->couchpotato->enabled == '1' ? 'checked' : null
		]);

		$this->fields[] = $couchpotatoURL = new Text('couchpotato-url');
		$couchpotatoURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->couchpotato->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'couchpotato-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $couchpotatoAPIKey = new Password('couchpotato-api_key');
		$couchpotatoAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->couchpotato->api_key)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'couchpotato-enabled']));

		$this->fields[] = $rotateInterval = new Numeric('couchpotato-rotate_interval');
		$rotateInterval->setLabel('Rotate interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->couchpotato->rotate_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'couchpotato-enabled'])
			]);

        $this->fields[] = $tmdbAPIURL = new Text('couchpotato-tmdb_api_url');
        $tmdbAPIURL->setLabel('TMDB API URL')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => true])
            ->setDefault($this->form->settings->couchpotato->tmdb_api_url)
            ->addValidators([
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'couchpotato-enabled'])
			]);

        $this->fields[] = $tmdbAPIKey = new Password('couchpotato-tmdb_api_key');
        $tmdbAPIKey->setLabel('TMDB API key')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
            ->setDefault($this->form->settings->couchpotato->tmdb_api_key)
            ->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'couchpotato-enabled'])
			]);
	}
}