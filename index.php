<?php

namespace Chell;

/**
 * The bootstrapper for this application
 */

require_once('app/FrontController.php');
echo (new FrontController())->ToString();