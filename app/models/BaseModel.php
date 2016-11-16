<?php
use Phalcon\Mvc\Model;

/**
 * The Base Model used by all models. Sets the config object to a variable.
 * 
 * @package Models
 * @todo	This doesn't work anymore in Phalcon3/PHP7
 */
class BaseModel extends Model
{
    protected $config;

    /**
     * Sets the config object to $this->config.
     */
    public function initialize()
    {
        $this->config = $this->di->get('config');
    }
}
