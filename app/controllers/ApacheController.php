<?php

namespace Chell\Controllers;

use Chell\Models\Apache;
use Chell\Controllers\WidgetController;

class ApacheController extends WidgetController
{
    private Apache $_model;

    /**
     * Initializes the controller, creating a new Opcache model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Apache();
    }

    public function indexAction()
    {
        \Chell\dump( $this->_model->getFpmStatus());
    }
}