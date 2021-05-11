<?php

namespace Chell\Controllers;

use Chell\Models\Jellyfin;

class JellyfinController extends BaseController
{
    public function viewsAction()
    {
        die(json_encode(array_flip((new Jellyfin())->getViews())));
    }
}
