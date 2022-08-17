<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Numericality;
use Phalcon\Filter\Validation\Validator\Ip;

/**
 * The formfields for the Roborock plugin
 *
 * @package Formfields
 */
class RoborockFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('roborock-enabled', [
			'fieldset' => 'Roborock',
			'checked' => $this->form->settings->roborock->enabled == '1' ? 'checked' : null
		]);

        $this->fields[] =  $roborockInterval = new Numeric('roborock-update_interval');
		$roborockInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->roborock->update_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'roborock-enabled'])
			]);

		$this->fields[] = $roborockIp = new Text('roborock-ip');
		$roborockIp->setLabel('IP')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->roborock->ip)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'roborock-enabled']),
				new Ip(['message' => $this->form->translator->validation['ip'], 'allowPrivate' => true, 'allowEmpty' => true]),
			]);

		$this->fields[] = $roborockToken = new Password('roborock-token');
		$roborockToken->setLabel('Token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->roborock->token)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'roborock-enabled']));
	}
}