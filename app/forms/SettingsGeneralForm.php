<?php

namespace Chell\Forms;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

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
        $this->setAction($this->config->application->baseUri . 'settings/general');

        $title = new Text('title');
        $title->setLabel('Title')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->config->application->title)
            ->addValidators([new PresenceOf(['message' => $this->translator->validation['required']])]);

        $bgcolor = new Select(
            'bgcolor',
            ['autobg' => 'Auto', 'darkbg' => 'Dark', 'lightbg' => 'Light', 'timebg' => 'Time based'],
            ['useEmpty' => false]
        );
        $bgcolor->setLabel('Background color')
            ->setDefault($this->config->application->background);

        $bgColorLatitude = new Numeric('bgcolor-latitude');
        $bgColorLatitude->setLabel('Latitude')
            ->setFilters(['striptags', 'float'])
            ->setAttributes(['class' => 'form-control location latitude' . ($this->config->application->background != 'timebg' ? 'hidden' : null), 'step' => 'any'])
            ->setDefault($this->config->application->backgroundLatitude)
            ->addValidator(new Numericality(['message' => $this->translator->validation['not-a-number']]));

        $bgColorLongitude= new Numeric('bgcolor-longitude');
        $bgColorLongitude->setLabel('Longitude')
            ->setFilters(['striptags', 'float'])
            ->setAttributes(['class' => 'form-control location longitude' . ($this->config->application->background != 'timebg' ? 'hidden' : null), 'step' => 'any'])
            ->setDefault($this->config->application->backgroundLongitude)
            ->addValidator(new Numericality(['message' => $this->translator->validation['not-a-number']]));

        $alertTimeout = new Numeric('alert-timeout');
        $alertTimeout->setLabel('Alert timeout')
            ->setFilters(['striptags', 'int'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->config->application->alertTimeout)
            ->addValidators([
                new PresenceOf(['message' => $this->translator->validation['required']]),
                new Numericality(['message' => $this->translator->validation['not-a-number']])
            ]);

        $itemsPerPage = new Numeric('items-per-page');
        $itemsPerPage->setLabel('Items per page')
            ->setFilters(['striptags', 'int'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->config->application->itemsPerPage)
            ->addValidators([
                new PresenceOf(['message' => $this->translator->validation['required']]),
                new Numericality(['message' => $this->translator->validation['not-a-number']])
            ]);

        $cryptKey = new Password('cryptkey');
        $cryptKey->setLabel('Cryptkey')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->config->application->phalconCryptKey)
            ->addValidator(new PresenceOf(['message' => $this->translator->validation['required']]));

        $tmdbAPIKey = new Password('tmdb-apikey');
        $tmdbAPIKey->setLabel('TMDB API key')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->config->application->tmdbAPIKey);

        $whatIsMyBrowserAPIKey = new Password('whatismybrowser-apikey');
        $whatIsMyBrowserAPIKey->setLabel('WhatIsMyBrowser API key')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->config->application->whatIsMyBrowserAPIKey);

        $whatIsMyBrowserAPIURL = new Text('whatismybrowser-apiurl');
        $whatIsMyBrowserAPIURL->setLabel('WhatIsMyBrowser API URL')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->config->application->whatIsMyBrowserAPIURL)
            ->addValidator(new UrlValidator(['message' => $this->translator->validation['url']]));

        $debug = new Check('debug');
        $debug->setLabel('Debug')
            ->setAttributes([
                'checked' => $this->config->application->debug == '1' ? 'checked' : null,
                'data-toggle' => 'toggle',
                'data-onstyle' => 'success',
                'data-offstyle' => 'danger',
                'data-size' => 'small'
        ]);

        $demo = new Check('demo');
        $demo->setLabel('Demo mode')
            ->setAttributes([
                'checked' => $this->config->application->demoMode == '1' ? 'checked' : null,
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
        $this->add($tmdbAPIKey);
        $this->add($whatIsMyBrowserAPIKey);
        $this->add($whatIsMyBrowserAPIURL);
        $this->add($debug);
        $this->add($demo);

        $this->setFormFieldClasses('General');
    }

    /**
     * Check if form is valid. If so set the values to the config array.
     *
     * @param   array     $data     The form data posted.
     * @param   object    $entity   The entity to validate.
     * @return  bool                Whether or not form is valid.
     */
    public function isValid($data = null, $entity = null) : bool
    {
        $valid = parent::isValid($data, $entity);

        if ($valid)
        {
            $this->config->application->title = $data['title'];
            $this->config->application->background = $data['bgcolor'];
            $this->config->application->backgroundLatitude = $data['bgcolor-latitude'];
            $this->config->application->backgroundLongitude = $data['bgcolor-longitude'];
            $this->config->application->alertTimeout = $data['alert-timeout'];
            $this->config->application->itemsPerPage = $data['items-per-page'];
            $this->config->application->phalconCryptKey = $data['cryptkey'];
            $this->config->application->tmdbAPIKey = $data['tmdb-apikey'];
            $this->config->application->whatIsMyBrowserAPIKey = $data['whatismybrowser-apikey'];
            $this->config->application->whatIsMyBrowserAPIURL = $data['whatismybrowser-apiurl'];
            $this->config->application->debug = isset($data['debug']) && $data['debug']== 'on' ? '1' : '0';
            $this->config->application->demoMode = isset($data['demo']) && $data['demo'] == 'on' ? '1' : '0';

            foreach($this->formFieldClasses as $class)
            {
                $class->setPostData($this->config, $data);
            }
        }

        return $valid;
    }
}
