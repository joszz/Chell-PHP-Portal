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

        $this->assets->scripts = array_merge($this->assets->scripts, $this->jsFiles);
        $this->assets->styles = array_merge($this->assets->styles, $this->cssFiles);
    }
}