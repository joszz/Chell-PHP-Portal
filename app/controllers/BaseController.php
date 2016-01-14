<?php
use Phalcon\Mvc\Controller;

/**
 * The baseController used by all controllers. Loads the config.ini to a variable
 * 
 * @package Controllers
 */
class BaseController extends Controller
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
