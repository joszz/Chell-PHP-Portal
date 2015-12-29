<?php
use Phalcon\Mvc\Controller;

class BaseController extends Controller
{
    protected $config;

    public function initialize()
    {
        $this->config = $this->di->get('config');   
    }
}
