<?php

namespace Chell\Forms;

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
        $this->setFormFieldClasses('General');
    }
}
