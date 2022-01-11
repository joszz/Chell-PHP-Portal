<?php

namespace Chell\Models;

use Chell\Models\SettingsContainer;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Url;

/**
 * The base model used by all other models.
 *
 * @package Models
 * @suppress PHP2414
 */
class BaseModel extends Model
{
    protected SettingsContainer $_settings;
    protected Url $url;

    /**
     * Initializes the model, getting settings and creating a new Url object.
     */
    public function initialize()
    {
        $this->url = new Url();
        $this->_settings = $this->di->get('settings');
    }
}