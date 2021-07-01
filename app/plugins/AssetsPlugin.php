<?php
namespace Chell\Plugins;

use Phalcon\Events\Event;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\View;

class AssetsPlugin extends Injectable
{
    public $scripts = [
        'jquery',
        'bootstrap',
        'jquery.fancybox',
        'jquery.vibrate',
        'jquery.fullscreen',
        'waves',
        'general',
    ];

    public $styles = [
        'jquery.fancybox',
        'waves',
        'default'
    ];

    public function beforeRender(Event $event, View $view) : bool
    {
        foreach ($this->scripts as $jsFile)
        {
            $file = $jsFile . (DEBUG ? '.js' : '.min.js');

            if (file_exists(APP_PATH . 'dist/js/' . $file))
            {
                $this->assets->collection('scripts')->addJs('dist/js/' . $file, true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            }
        }

        foreach ($this->styles as $cssFile)
        {
            $file = $cssFile . (DEBUG ? '.css' : '.min.css');

            if (file_exists(APP_PATH . 'dist/css/' . $file))
            {
                $this->assets->collection('styles')->addCss('dist/css/' . $file, true, false, [], $this->settings->application->version, true);
            }
        }

        return true;
    }
}