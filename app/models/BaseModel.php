<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The base model used by all other models.
 *
 * @package Models
 */
class BaseModel extends Model
{
    protected $_settings;

    /**
     * Sets the database relations
     */
    public function initialize()
    {
        $this->_settings = $this->di->get('settings');
    }
}