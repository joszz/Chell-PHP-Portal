<?php

namespace Chell\Controllers;

use Chell\Models\Couchpotato;

/**
 * The controller responsible for all CouchPotato related actions.
 *
 * @package Controllers
 */
class CouchpotatoController extends BaseController
{
	private $_model;

	/**
	 * Set the default layout to empty.
	 */
	public function initialize()
	{
		parent::initialize();

		$this->view->setMainView('layouts/empty');
		$this->_model = new Couchpotato();
	}

	/**
	 * Retrieves movie details from CouchPotato API.
	 *
	 * @param string $id The CouchPotate ID to use to call the API with.
	 */
	public function movieAction($id)
	{
		$this->view->movie = $movie = $this->_model->getMovie($id);
		$this->view->bgImage = current($movie->info->images->backdrop_original);
	}
}