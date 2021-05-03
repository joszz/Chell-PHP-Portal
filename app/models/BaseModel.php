<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

class BaseModel extends Model
{
    protected $_config;

    /**
     * Sets the database relations
     */
    public function initialize()
    {
        $this->_config = $this->di->get('config');
    }
}