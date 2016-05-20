<?php

try 
{
    require_once('FrontController.php');
    echo (new FrontController())->tostring();
}
catch (\Exception $e) 
{
    echo 'PhalconException: ', $e->getMessage();
}