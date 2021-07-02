<?php

namespace Chell\Controllers;

use Chell\Controllers\BaseController;

class WidgetController extends BaseController
{
    protected array $jsFiles = [];
    protected array $cssFiles = [];

    public function initialize()
    {
        parent::initialize();

        $this->assets->addScripts($this->jsFiles)->addStyles($this->cssFiles);
    }
}