<?php

namespace Chell\Forms;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Mvc\Model;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;

/**
 * The form responsible for the general settings.
 *
 * @package Forms
 */
class SettingsGeneralForm extends SettingsBaseForm
{
    /**
     * Add all fields to the form and set form specific attributes.
     */
    public function initialize()
    {
        $this->setAction($this->settings->application->base_uri . 'settings/general');

        $title = new Text('title');
        $title->setLabel('Title')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->settings->application->title)
            ->addValidators([new PresenceOf(['message' => $this->translator->validation['required']])]);

        $bgcolor = new Select(
            'bgcolor',
            ['autobg' => 'Auto', 'darkbg' => 'Dark', 'lightbg' => 'Light', 'timebg' => 'Time based'],
            ['useEmpty' => false]
        );
        $bgcolor->setLabel('Background color')
            ->setDefault($this->settings->application->background);

        $bgColorLatitude = new Numeric('bgcolor-latitude');
        $bgColorLatitude->setLabel('Latitude')
            ->setFilters(['striptags', 'float'])
            ->setAttributes(['class' => 'form-control location latitude' . ($this->settings->application->background != 'timebg' ? 'hidden' : null), 'step' => 'any'])
            ->setDefault($this->settings->application->background_latitude)
            ->setUserOptions(['buttons' => ['location']])
            ->addValidator(new Numericality(['message' => $this->translator->validation['not-a-number']]));

        $bgColorLongitude= new Numeric('bgcolor-longitude');
        $bgColorLongitude->setLabel('Longitude')
            ->setFilters(['striptags', 'float'])
            ->setAttributes(['class' => 'form-control location longitude' . ($this->settings->application->background != 'timebg' ? 'hidden' : null), 'step' => 'any'])
            ->setDefault($this->settings->application->background_longitude)
            ->setUserOptions(['buttons' => ['location']])
            ->addValidator(new Numericality(['message' => $this->translator->validation['not-a-number']]));

        $alertTimeout = new Numeric('alert-timeout');
        $alertTimeout->setLabel('Alert timeout')
            ->setFilters(['striptags', 'int'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->settings->application->alert_timeout)
            ->addValidators([
                new PresenceOf(['message' => $this->translator->validation['required']]),
                new Numericality(['message' => $this->translator->validation['not-a-number']])
            ]);

        $itemsPerPage = new Numeric('items-per-page');
        $itemsPerPage->setLabel('Items per page')
            ->setFilters(['striptags', 'int'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->settings->application->items_per_page)
            ->addValidators([
                new PresenceOf(['message' => $this->translator->validation['required']]),
                new Numericality(['message' => $this->translator->validation['not-a-number']])
            ]);

        $cryptKey = new Password('cryptkey');
        $cryptKey->setLabel('Cryptkey')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->settings->application->phalcon_crypt_key)
            ->addValidator(new PresenceOf(['message' => $this->translator->validation['required']]));

        $devicestateTimeouts = new Numeric('check-devicestate-interval');
        $devicestateTimeouts->setLabel('Check device state interval')
            ->setFilters(['striptags', 'int'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->settings->dashboard->check_device_states_interval)
            ->addValidator(new Numericality(['message' => 'Not a number']));

        $demo = new Check('demo');
        $demo->setLabel('Demo mode')
            ->setAttributes([
                'checked' => $this->settings->application->demo_mode == '1' ? 'checked' : null,
                'data-toggle' => 'toggle',
                'data-onstyle' => 'success',
                'data-offstyle' => 'danger',
                'data-size' => 'small'
        ]);

        $this->add($title);
        $this->add($bgcolor);
        $this->add($bgColorLatitude);
        $this->add($bgColorLongitude);
        $this->add($alertTimeout);
        $this->add($itemsPerPage);
        $this->add($cryptKey);
        $this->add($devicestateTimeouts);
        $this->add($demo);

        $this->setFormFieldClasses('General');
    }

    /**
     * Check if form is valid. If so set the values to the config array.
     *
     * @param   array     $data     The form data posted.
     * @param   Model     $entity   The entity to validate.
     * @return  bool                Whether or not form is valid.
     */
    public function isValid( $data = null, $entity = null) : bool
    {
        $valid = parent::isValid($data, $entity);

        if ($valid)
        {
            $this->settings->application->title = $data['title'];
            $this->settings->application->background = $data['bgcolor'];
            $this->settings->application->background_latitude = $data['bgcolor-latitude'];
            $this->settings->application->background_longitude = $data['bgcolor-longitude'];
            $this->settings->application->alert_timeout = $data['alert-timeout'];
            $this->settings->application->items_per_page = $data['items-per-page'];
            $this->settings->application->phalcon_crypt_key = $data['cryptkey'];
            $this->settings->dashboard->check_device_states_interval = $data['check-devicestate-interval'];
            $this->settings->application->demo_mode = isset($data['demo']) && $data['demo'] == 'on' ? '1' : '0';

            foreach ($this->formFieldClasses as $class)
            {
                $class->setPostData($this->settings, $data);
            }
        }

        return $valid;
    }
}
