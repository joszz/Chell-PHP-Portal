<?php

namespace Chell\Forms\FormFields;

interface IFormFields
{
    /**
     * Adds fields to the form.
     */
    function setFields($form);

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    function setPostData(&$config, $data);
}