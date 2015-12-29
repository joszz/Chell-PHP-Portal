<?php

try 
{
    require_once('FrontController.php');
    $fc = new FrontController();
    echo $fc->tostring();
}
catch (\Exception $e) 
{
    echo 'PhalconException: ', $e->getMessage();
}