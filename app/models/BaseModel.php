<?php

namespace Chell\Models;

use Chell\Models\SettingsContainer;
use Phalcon\Mvc\Model;
use Phalcon\Url;

/**
 * The base model used by all other models.
 *
 * @package Models
 */
class BaseModel extends Model
{
    protected SettingsContainer $_settings;
    protected Url $url;

    /**
     * Sets the database relations
     */
    public function initialize()
    {
        $this->url = new Url();
        $this->_settings = $this->di->get('settings');
    }
}