<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Numericality;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

/**
 * The formfields for the Apache plugin
 *
 * @package Formfields
 */
class ApacheFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('apache-enabled', [
			'fieldset' => 'Apache',
			'checked' => $this->form->settings->apache->enabled == '1' ? 'checked' : null,
		]);

		$this->fields[] = $apacheServerStatusURL = new Text('apache-server_status_url');
		$apacheServerStatusURL->setLabel('Server status URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->apache->server_status_url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'apache-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $apacheFpmStatusUrl = new Text('apache-fpm_status_url');
		$apacheFpmStatusUrl->setLabel('FPM status URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->apache->fpm_status_url)
			->addValidators([
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

        $this->fields[] = $apacheInterval = new Numeric('apache-update_interval');
		$apacheInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->pulseway->update_interval ?? 30)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'databasestats-enabled'])
			]);
	}
}