<?php

namespace Chell\Controllers;

use ReflectionClass;
use DirectoryIterator;
use Chell\Models\Devices;
use Chell\Models\Jellyfin;
use Chell\Models\Motion;
use Chell\Models\SnmpHosts;
use Chell\Models\WidgetPosition;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Mvc\View;

/**
 * The controller responsible for all dashboard related actions.
 *
 * @package Controllers
 */
class IndexController extends BaseController
{
    /**
     * Shows the dashboard view
     */
    public function indexAction()
    {
        $this->setWidgets();
        $this->view->dnsPrefetchRecords = $this->setDNSPrefetchRecords();
        $this->view->devices = Devices::find(['order' => 'name ASC']);
        $this->view->maxWidgetPosition = $this->getMaxWidgetPosition();

        if ($this->settings->jellyfin->enabled->value)
        {
            $jellyfin = new Jellyfin();
            $this->view->jellyfinviews = $jellyfin->getLatestForViews();
        }

        if ($this->settings->motion->enabled->value)
        {
            $this->view->motionModifiedTime = (new Motion())->getModifiedTime();
        }

        if ($this->settings->snmp->enabled->value)
        {
            $this->view->snmpHosts = SnmpHosts::find();
        }
    }

    /**
     * Renders the manifest.json file, used for favicons and the like.
     */
    public function manifestAction()
    {
        $this->response->setContentType('application/json', 'charset=UTF-8');
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }

    /**
     * The stub service worker. Setting header for allowing service worker from root.
     */
    public function workerAction()
    {
        $this->response->setHeader('Service-Worker-allowed', '../');
        $this->response->setContentType('text/javascript');
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }

    /**
     * Moves a widget position up or down, swapping the position with the widget already there.
     *
     * @param string $direction     Either 'up' or 'down', moving the widget in that direction on the dashboard.
     * @param int $id               The id of the widget to move.
     */
    public function movewidgetAction(string $direction, int $id)
    {
        $widgetToMove = WidgetPosition::findFirst([
            'conditions' => 'id = ?1',
            'bind'       => [1 => $id],
        ]);
        $maxPosition = $this->getMaxWidgetPosition();
        $widgetToSwapPlaceWith = null;

        if ($direction === 'down' && $widgetToMove->position + 1 < $maxPosition)
        {
            $widgetToMove->position += 1;
            $widgetToSwapPlaceWith = WidgetPosition::findFirst([
                'conditions' => 'position = ?1',
                'bind'       => [1 => $widgetToMove->position],
            ]);
            $widgetToSwapPlaceWith->position -= 1;
        }
        else if ($widgetToMove->position > 1)
        {
            $widgetToMove->position -= 1;
            $widgetToSwapPlaceWith = WidgetPosition::findFirst([
                'conditions' => 'position = ?1',
                'bind'       => [1 => $widgetToMove->position],
            ]);
            $widgetToSwapPlaceWith->position += 1;
        }

        if ($widgetToSwapPlaceWith)
        {
            $transactionManager = new TransactionManager();
            $transaction = $transactionManager->get();
            $widgetToMove->setTransaction($transaction);
            $widgetToSwapPlaceWith->setTransaction($transaction);

            if ($widgetToMove->save() === false || $widgetToSwapPlaceWith->save() === false)
            {
                $transaction->rollback();
            }
            else
            {
                $transaction->commit();
            }
        }

        $this->response->redirect('?moveWidgetVisible=true');
    }

    public function healthcheckAction()
    {
        die();
    }

    /**
     * Sets the URLs for <link rel="dns-prefetch" />
     *
     * @return array An array with strings for the DNS prefetch URLs
     */
    private function setDNSPrefetchRecords() : array
    {
        $hostname = getenv('HTTP_HOST');
        $dnsPrefetchRecords = [];

        foreach($this->settings as $settingSectionValue)
        {
            foreach($settingSectionValue as $settingKey => $setting)
            {
                // Disabled in the config so skip complete section
                if (strtolower($settingKey) == 'enabled' && !$setting)
                {
                    continue 2;
                }

                if (strtolower($settingKey) == 'url')
                {
                    $parsedURL = parse_url($setting->value);

                    if (isset($parsedURL['host'] ) && $parsedURL['host'] != $hostname)
                    {
                        $dnsPrefetchRecords[] = $parsedURL['scheme'] . '://' . $parsedURL['host'];
                    }
                }
            }
        }

        return $dnsPrefetchRecords;
    }

    /**
     * Retrives the maximum position of all widgets.
     *
     * @return int  The maximum position of all widgets
     */
    private function getMaxWidgetPosition()
    {
        return WidgetPosition::maximum(['column' => 'position']);
    }

    /**
     * Sets the styles and scripts for the widgets that are enabled.
     * Iterates through all controllers and uses reflection to get Controllers that are a subclass of WidgetController.
     * For those controllers, the properties jsFiles and cssFiles are retrieved and added to the collection of JS/CSS to render in the HTML.
     * Adds the controller name (minus the string 'Controller') to the JS/CSS to render in the HTML.
     * And finally adds some default JS/CSS that is used in the dashboard.+
     *
     * @todo Refactor this, seperate widget logic and asset logic.
     */
    private function setWidgets()
    {
        $dir = new DirectoryIterator(APP_PATH . 'app/controllers/');
        $scripts = ['jquery.tinytimer', 'jquery.isloading'];
        $styles = ['dashboard'];
        $widgets = [];
        $widgetPositions = WidgetPosition::find(['order' => 'position']);
        $currentClass = [get_class($this), get_parent_class($this)];

        foreach ($dir as $fileinfo)
        {
            $file = $fileinfo->getFilename();
            $controllerName = __NAMESPACE__ . '\\' . basename($file, '.' . $fileinfo->getExtension());

            if (!$fileinfo->isDot() && !in_array($controllerName, $currentClass))
            {
                $controller = new ReflectionClass($controllerName);

                if ($controller->isSubclassOf(__NAMESPACE__ . '\WidgetController'))
                {
                    $name = strtolower(str_replace('Controller', '', str_replace(__NAMESPACE__ . '\\', '', $controller->getName())));
                    $controllerInstance = $controller->newInstanceWithoutConstructor();
                    $controllerInstance->addAssets();
                    $controllerInstance->setPanelSize();

                    $jsFilesProperty = $controller->getProperty('jsFiles');
                    $jsFilesProperty->setAccessible(true);
                    $scripts = [...$jsFilesProperty->getValue($controllerInstance), ...$scripts];

                    $cssFilesProperty = $controller->getProperty('cssFiles');
                    $cssFilesProperty->setAccessible(true);
                    $styles = [...$cssFilesProperty->getValue($controllerInstance), ...$styles];

                    if (isset($this->settings->{$name}) && $this->settings->{$name}->enabled->value)
                    {
                        $this->addWidget($controller, $controllerInstance, $widgetPositions, $name, $widgets);

                        if (is_file(PUBLIC_PATH . 'js/' . $name . '.js'))
                        {
                            $scripts[] = $name;
                        }

                        if (is_file(PUBLIC_PATH . 'css/' . $name . '.css'))
                        {
                            $styles[] = $name;
                        }
                    }
                }
            }
        }

        ksort($widgets);
        $this->view->widgets = $widgets;

        $scripts[] = 'dashboard';

        $this->assets->addScripts($scripts)->addStyles($styles);
    }

    /**
     * Adds a widget to the array of widgets, passed by reference.
     * Each controller that inherits from WidgetController, has a Widget class.
     * Retrieve this Widget class through reflection (using $controller and $controllerInstance).
     * Set the widget in the correct position in the array (the key of $widgets) and set the Id and the partial to use to use as the widget's view.
     *
     * @param ReflectionClass $controller           Used to retrieve the Widget class.
     * @param Controller $controllerInstance        Used to retrieve the Widget class.
     * @param ResultsetInterface $widgetPositions   All the widget positions in one resultset.
     * @param string $controllerName                The name of the controller the widget belongs to.
     * @param array $widgets                        All the widgets in an array so far.
     */
    private function addWidget(ReflectionClass $controller, Controller $controllerInstance, ResultsetInterface $widgetPositions, string $controllerName, array &$widgets)
    {
        $widgetProperty = $controller->getProperty('widget');
        $widgetProperty->setAccessible(true);
        $widget = $widgetProperty->getValue($controllerInstance);

        $currentWidgetPositions = $widgetPositions->filter(function($widgetPostion) use($controllerName) {
            if ($widgetPostion->controller == $controllerName)
            {
                return $widgetPostion;
            }
        });

        foreach ($currentWidgetPositions as $currentWidgetPosition)
        {
            $widget = clone $widget;
            $widget->id = $currentWidgetPosition->id;
            $widget->partial = $currentWidgetPosition->controller . '/' . $currentWidgetPosition->widget_viewname;
            $widget->position = $currentWidgetPosition->position;
            $widgets[$currentWidgetPosition->position] = $widget;
        }
    }
}