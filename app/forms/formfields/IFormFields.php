<?php

namespace Chell\Forms\FormFields;

use Chell\Forms\SettingsBaseForm;
use Chell\Models\SettingsContainer;

interface IFormFields
{
    /**
     * Adds fields to the form.
     */
    function setFields(SettingsBaseForm $form);

    /**
     * Sets the post data to the config variables
     *
     * @param object $settings	The settings object
     * @param array $data		The posted data
     */
    function setPostData(SettingsContainer &$settings, array $data);
}