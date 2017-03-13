<?php

/**
 * The bootstrapper for this application
 */

try 
{
    require_once('../app/FrontController.php');
    echo (new FrontController())->tostring();
}
catch (\Exception $e) 
{
    echo 'PhalconException: ', $e->getMessage();
}