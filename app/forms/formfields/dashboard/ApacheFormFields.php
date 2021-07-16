<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class ApacheFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = $apacheEnabled = new Check('apache-enabled');
		$apacheEnabled->setLabel('Enabled')
			->setAttributes([
				'value' => '1',
				'checked' => $this->form->settings->apache->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Apache'
		]);

		$this->fields[] = $apacheServerStatusURL = new Text('apache-server_status_url');
		$apacheServerStatusURL->setLabel('Server status URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->apache->server_status_url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'apache-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $apacheFpmStatusUrl = new Text('apache-fpm_status_url');
		$apacheFpmStatusUrl->setLabel('FPM status URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->apache->fpm_status_url)
			->addValidators([
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

        $this->fields[] = $apacheInterval = new Numeric('apache-update_interval');
		$apacheInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->form->settings->pulseway->update_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'databasestats-enabled'])
			]);
	}
}