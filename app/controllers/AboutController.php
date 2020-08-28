<?php

namespace Chell\Controllers;

/**
 * The controller responsible for showing about information of this project.
 *
 * @package Controllers
 */
class AboutController extends BaseController
{
    /**
     * Shows version information and has link to code documentation.
     */
    public function indexAction()
    {
        $this->view->containerFullHeight = true;

        $this->view->versionMajor = 0;
        $this->view->versionMinor = 1;
        $this->view->versionStability = '&alpha;';
    }
}