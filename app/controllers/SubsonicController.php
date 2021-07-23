<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;

/**
 * The controller responsible for all Subsonic widget related actions.
 *
 * @package Controllers
 */
class SubsonicController extends WidgetController
{
	protected array $jsFiles = ['nowplaying', 'spark-md5'];
}