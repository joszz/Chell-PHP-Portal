<?php
namespace Chell\Plugins;

use Phalcon\Events\Event;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\View;

/**
 * Handles all JS/CSS assets for the application.
 *
 * @package Plugins
 */
class AssetsPlugin extends Injectable
{
    private array $scripts = [
        'jquery',
        'bootstrap',
        'jquery.fancybox',
        'jquery.vibrate',
        'jquery.fullscreen',
        'waves'
    ];

    private array $styles = [
        'jquery.fancybox',
        'waves',
        'default'
    ];

    /**
     * Called before rendering the view.
     * Sets the scripts and styles collection, either with minified files or not (when DEBUGging).
     *
     * @param Event $event  The beforeRender event
     * @param View $view    The view being rendered
     * @return bool         Success or failure.
     */
    public function beforeRender(Event $event, View $view) : bool
    {
        foreach (array_unique($this->scripts) as $jsFile)
        {
            $file = 'dist/js/' . $jsFile . (DEBUG ? '.js' : '.min.js');

            if (file_exists(APP_PATH . $file))
            {
                $this->assets->collection('scripts')->addJs(BASEPATH . $file, true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            }
        }

        $this->assets->collection('scripts')->addJs(BASEPATH . 'dist/js/general' . (DEBUG ? '.js' : '.min.js'), true, false, ['defer' => 'defer'], $this->settings->application->version, true);

        foreach (array_unique($this->styles) as $cssFile)
        {
            $file = 'dist/css/' . $cssFile . (DEBUG ? '.css' : '.min.css');

            if (file_exists(APP_PATH . $file))
            {
                $this->assets->collection('styles')->addCss(BASEPATH . $file, true, false, [], $this->settings->application->version, true);
            }
        }

        return true;
    }

    /**
     * Adds a style.
     *
     * @param string $name  The basename of the style to add. No extension or directory.
     * @return AssetsPlugin This class, so you're able to chain calls.
     */
    public function addStyle(string $name) : AssetsPlugin
    {
        $this->styles[] = $name;
        return $this;
    }

    /**
     * Adds an array of styles.
     *
     * @param array $names  The basename of the styles to add. No extension or directory per item.
     * @return AssetsPlugin This class, so you're able to chain calls.
     */
    public function addStyles(array $names)
    {
        $this->styles = [...$this->styles, ...$names];
        return $this;
    }

    /**
     * Adds a script.
     *
     * @param string $name  The basename of the script to add. No extension or directory.
     * @return AssetsPlugin This class, so you're able to chain calls.
     */
    public function addScript(string $name)
    {
        $this->scripts[] = $name;
        return $this;
    }

    /**
     * Adds an array of scripts.
     *
     * @param array $names  The basename of the scripts to add. No extension or directory.
     * @return AssetsPlugin This class, so you're able to chain calls.
     */
    public function addScripts(array $names)
    {
        $this->scripts = [...$this->scripts, ...$names];
        return $this;
    }

    /**
     * Adds a script and a style with the same basename.
     *
     * @param string $name  The basename of the script and style to add. No extension or directory.
     * @return AssetsPlugin This class, so you're able to chain calls.
     */
    public function addStyleAndScript(string $name)
    {
        $this->addStyle($name);
        $this->addScript($name);
        return $this;
    }

    /**
     * Adds an array of scripts and styles with the same basename.
     *
     * @param array $names  The basename of the scripts and styles to add. No extension or directory.
     * @return AssetsPlugin This class, so you're able to chain calls.
     */
    public function addStylesAndScripts(array $name)
    {
        $this->addStyles($name);
        $this->addScripts($name);
        return $this;
    }
}