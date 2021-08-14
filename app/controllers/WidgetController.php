<?php

namespace Chell\Controllers;

use Chell\Controllers\BaseController;

/**
 * The base for all Controllers that represent a widget.
 *
 * @package Controllers
 */
class WidgetController extends BaseController
{
    protected array $jsFiles = [];
    protected array $cssFiles = [];

    public function initialize()
    {
        parent::initialize();

        $this->addAssets();
        $this->assets->addScripts($this->jsFiles)->addStyles($this->cssFiles);
    }

    public function addAssets()
    {
        return;
    }
}