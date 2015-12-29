<?php
use Phalcon\Mvc\Model;

class BaseModel extends Model
{
    protected $config;

    public function initialize()
    {
        $this->config = $this->di->get('config');   
    }
}
