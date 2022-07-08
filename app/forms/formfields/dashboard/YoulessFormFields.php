<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\Hibp;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Numericality;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

/**
 * The formfields for the Youless plugin
 *
 * @package Formfields
 */
class YoulessFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->fields[] = new Check('youless-enabled', [
			'fieldset' => 'Youless',
			'checked' => $this->form->settings->youless->enabled == '1' ? 'checked' : null
		]);

		$this->fields[] = $youlessURL = new Text('youless-url');
		$youlessURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->youless->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'youless-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $youlessPassword = new Password('youless-password');
		$youlessPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($this->form->settings->youless->password)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'youless-enabled']));

        if ($this->form->settings->hibp->enabled)
        {
			$youlessPassword->addValidator(new Hibp(['message' => $this->form->translator->validation['hibp']]));
        }

		$this->fields[] = $youlessInterval = new Numeric('youless-update_interval');
		$youlessInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->youless->update_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'youless-enabled'])
			]);

		$this->fields[] = $youlessPrimaryThreshold = new Numeric('youless-threshold_primary');
		$youlessPrimaryThreshold->setLabel('Primary threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->youless->threshold_primary)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'youless-enabled'])
			]);

		$this->fields[] = $youlessWarnThreshold = new Numeric('youless-threshold_warning');
		$youlessWarnThreshold->setLabel('Warn threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->youless->threshold_warning)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'youless-enabled'])
			]);

		$this->fields[] = $youlessDangerThreshold = new Numeric('youless-threshold_danger');
		$youlessDangerThreshold->setLabel('Danger threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->form->settings->youless->threshold_danger)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'youless-enabled'])
			]);
	}
}