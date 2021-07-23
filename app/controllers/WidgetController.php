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

    /**
     * Adds styles and sceripts based on what's set in the propeties of this class.
     */
    public function initialize()
    {
        parent::initialize();

        $this->assets->addScripts($this->jsFiles)->addStyles($this->cssFiles);
    }
}