<?php

namespace Chell\Controllers;

use Chell\Controllers\BaseController;
use Chell\Models\Widget;

/**
 * The base for all Controllers that represent a widget.
 *
 * @package Controllers
 */
abstract class WidgetController extends BaseController
{
    protected array $jsFiles = [];
    protected array $cssFiles = [];

    protected Widget $widget;

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

    public function setPanelSize()
    {
        $this->widget = new Widget(12, 6, 4);
    }
}