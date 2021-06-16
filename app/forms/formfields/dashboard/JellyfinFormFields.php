<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class JellyfinFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = $jellyfinEnabled = new Check('jellyfin-enabled');
		$jellyfinEnabled->setLabel('Enabled')
			->setAttributes([
				'value' => '1',
				'checked' => $this->form->settings->jellyfin->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Jellyfin'
		]);

        $this->fields[] = $jellyfinUrl = new Text('jellyfin-url');
		$jellyfinUrl->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->jellyfin->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'jellyfin-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $jellyfinToken = new Password('jellyfin-token');
		$jellyfinToken->setLabel('Token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->jellyfin->token)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'jellyfin-enabled']));

        $this->fields[] = $jellyfinUserId = new Text('jellyfin-userid');
		$jellyfinUserId->setLabel('User id')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->jellyfin->userid)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'jellyfin-enabled']));

		$this->fields[] = $jellyfinRotateInterval = new Numeric('jellyfin-rotate_interval');
		$jellyfinRotateInterval->setLabel('Rotate interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->jellyfin->rotate_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'jellyfin-enabled'])
		]);

		$this->fields[] = $jellyfinViews = new Select('jellyfin-views[]');
		$jellyfinViews->setLabel('Libraries')
			->setFilters(['striptags', 'string'])
			->setAttributes([
				'class' => 'form-control',
				'multiple' => 'multiple',
				'fieldset' => 'end',
				'data-selected' => $this->form->settings->jellyfin->views,
				'data-apiurl' => '../jellyfin/views'
			])
			->setUserOptions(['buttons' => ['refresh_api_data']]);
	}
}