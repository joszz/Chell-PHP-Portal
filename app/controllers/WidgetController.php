<?php

namespace Chell\Controllers;

use Chell\Controllers\BaseController;

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

class Widget
{
    public string $viewFileName;
    public int $xs;
    public int $sm;
    public int $md;

    public function __construct(int $xs = 12, int $sm = 0, int $md = 0)
    {
        $this->xs = $xs;
        $this->sm = $sm;
        $this->md = $md;
    }

    public function getClass()
    {
        $class = 'col-xs-' . $this->xs;
        $class .=  $this->sm != 0 ? ' col-sm-' . $this->sm : null;
        $class .=  $this->md != 0 ? ' col-md-' . $this->md : null;

        return $class;
    }
}