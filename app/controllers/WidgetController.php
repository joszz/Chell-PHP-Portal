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

    /**
     * Initializes the controller, calling addAssets and adding the assets to the Assets plugin.
     */
    public function initialize()
    {
        parent::initialize();

        $this->addAssets();
        $this->assets->addScripts($this->jsFiles)->addStyles($this->cssFiles);
    }

    /**
     * Adds default assets, empty.
     * 
     * @return void
     */
    public function addAssets()
    {
        return;
    }

    /**
     * Sets the default Bootstrap panel size for the widget.
     */
    public function setPanelSize()
    {
        $this->widget = new Widget(12, 6, 4);
    }
}