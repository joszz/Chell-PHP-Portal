<?php
use Phalcon\Mvc\Model;

/**
 * The Base Model used by all models. Sets the config object to a variable.
 * 
 * @package Models
 */
class BaseModel extends Model
{
    protected $config;

    /**
     * Sets the config object to $this->config.
     * 
     * @return  void
     */
    public function initialize()
    {
        $this->config = $this->di->get('config');   
    }
}
