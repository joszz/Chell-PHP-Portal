<?php

namespace Chell\Controllers;

use Chell\Models\CouchPotato;

/**
 * The controller responsible for all CouchPotato related actions.
 *
 * @package Controllers
 */
class CouchpotatoController extends BaseController
{
	/**
	 * Set the default layout to empty.
	 */
	public function initialize()
	{
		$this->view->setMainView('layouts/empty');
		parent::initialize();
	}

	/**
	 * Retrieves movie details from CouchPotato API.
	 *
	 * @param string $id    The CouchPotate ID to use to call the API with.
	 */
	public function movieAction($id)
	{
		$this->view->movie = $movie = CouchPotato::getMovie($id, $this->config);
		$this->view->bgImage = current($movie->info->images->backdrop_original);
	}
}