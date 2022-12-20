<?php

namespace Chell\Forms\FormFields\General;

use Chell\Forms\FormFields\FormFields;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Numericality;

/**
 * The formfields for the Apache plugin
 *
 * @package Formfields
 */
class ApplicationFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
        $this->fields[] = $title = new Text('application-title');
        $title->setLabel('Title')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->form->settings->application->title)
            ->addValidators([new PresenceOf(['message' => $this->form->translator->validation['required']])]);

        $this->fields[] = $bgcolor = new Select(
            'application-background',
            ['autobg' => 'Auto', 'darkbg' => 'Dark', 'lightbg' => 'Light', 'timebg' => 'Time based'],
            ['useEmpty' => false]
        );
        $bgcolor->setLabel('Background color')
            ->setDefault($this->form->settings->application->background);

        $this->fields[] = $bgColorLatitude = new Numeric('application-background_latitude');
        $bgColorLatitude->setLabel('Latitude')
            ->setFilters(['striptags', 'float'])
            ->setAttributes(['class' => 'form-control location latitude' . ($this->form->settings->application->background != 'timebg' ? 'hidden' : null), 'step' => 'any'])
            ->setDefault($this->form->settings->application->background_latitude)
            ->setUserOptions(['buttons' => ['location']])
            ->addValidator(new Numericality(['message' => $this->form->translator->validation['not-a-number']]));

        $this->fields[] = $bgColorLongitude= new Numeric('application-background_longitude');
        $bgColorLongitude->setLabel('Longitude')
            ->setFilters(['striptags', 'float'])
            ->setAttributes(['class' => 'form-control location longitude' . ($this->form->settings->application->background != 'timebg' ? 'hidden' : null), 'step' => 'any'])
            ->setDefault($this->form->settings->application->background_longitude)
            ->setUserOptions(['buttons' => ['location']])
            ->addValidator(new Numericality(['message' => $this->form->translator->validation['not-a-number']]));

        $this->fields[] = $alertTimeout = new Numeric('application-alert_timeout');
        $alertTimeout->setLabel('Alert timeout')
            ->setFilters(['striptags', 'int'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->form->settings->application->alert_timeout)
            ->addValidators([
                new PresenceOf(['message' => $this->form->translator->validation['required']]),
                new Numericality(['message' => $this->form->translator->validation['not-a-number']])
            ]);

        $this->fields[] =  $itemsPerPage = new Numeric('application-items_per_page');
        $itemsPerPage->setLabel('Items per page')
            ->setFilters(['striptags', 'int'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->form->settings->application->items_per_page)
            ->addValidators([
                new PresenceOf(['message' => $this->form->translator->validation['required']]),
                new Numericality(['message' => $this->form->translator->validation['not-a-number']])
            ]);

        $this->fields[] = $cryptKey = new Password('application-phalcon_crypt_key');
        $cryptKey->setLabel('Cryptkey')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->form->settings->application->phalcon_crypt_key)
            ->addValidator(new PresenceOf(['message' => $this->form->translator->validation['required']]));

        $this->fields[] = $devicestateTimeouts = new Numeric('application-check_device_states_interval');
        $devicestateTimeouts->setLabel('Check device state interval')
            ->setFilters(['striptags', 'int'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->form->settings->application->check_device_states_interval)
            ->addValidator(new Numericality(['message' => 'Not a number']));

        $this->fields[] = $hibp = new Check('hibp-enabled', [
            'checked' => $this->form->settings->hibp->enabled == '1' ? 'checked' : null
        ]);
        $hibp->setLabel('Have I Been Pwned');

        $this->fields[] = $demo = new Check('application-demo_mode', [
            'checked' => $this->form->settings->application->demo_mode == '1' ? 'checked' : null
        ]);
        $demo->setLabel('Demo mode');

        $this->fields[] = $demo = new Check('application-debug', [
            'checked' => $this->form->settings->application->debug == '1' ? 'checked' : null
        ]);
        $demo->setLabel('Debug mode');
	}
}