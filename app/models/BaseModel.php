<?php

namespace Chell\Models;

use Chell\Models\SettingsContainer;
use Chell\Plugins\ChellLogger;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Url;

/**
 * The base model used by all other models.
 *
 * @package Models
 */
class BaseModel extends Model
{
    protected SettingsContainer $settings;
    protected Url $url;
    protected ChellLogger $logger;

    /**
     * Initializes the model, getting settings and creating a new Url object.
     */
    public function initialize()
    {
        $this->url = new Url();
        $this->settings = $this->di->get('settings');
        $this->logger = $this->di->get('logger');
    }
}