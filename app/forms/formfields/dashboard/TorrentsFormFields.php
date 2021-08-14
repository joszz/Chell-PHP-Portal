<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\Hibp;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class TorrentsFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = $torrentsEnabled = new Check('torrents-enabled');
		$torrentsEnabled->setLabel('Enabled')
			->setAttributes([
				'value' => '1',
				'checked' => $this->form->settings->torrents->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Torrents'
		]);

        $this->fields[] = $speedtestISPInfo = new Select('torrents-client', ['qbittorrent' => 'qBittorrent', 'transmission' => 'Transmission']);
		$speedtestISPInfo->setLabel('Bittorrent client')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->torrents->client)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'torrents-enabled']));

        $this->fields[] = $torrentsURL = new Text('torrents-url');
		$torrentsURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->torrents->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'torrents-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $torrentsUsername = new Text('torrents-username');
		$torrentsUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->torrents->username)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'torrents-enabled']));

		$this->fields[] = $torrentsPassword = new Password('torrents-password');
		$torrentsPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($this->form->settings->torrents->password)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'torrents-enabled']));

        if ($this->form->settings->hibp->enabled)
        {
			$torrentsPassword->addValidator(new Hibp(['message' => $this->form->translator->validation['hibp']]));
        }

		$this->fields[] = $torrentsInterval = new Numeric('torrents-update_interval');
		$torrentsInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->form->settings->torrents->update_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'torrents-enabled'])
			]);
	}
}