<?php

namespace Chell\Forms;

use Phalcon\Forms\Element\ElementInterface;
use Phalcon\Forms\Form;

/**
 * The base form class used in SettingsGeneralForm and SettingsDashboardForm.
 *
 * @package Forms
 */
class SettingsBaseForm extends Form
{
    /**
     * The configuration object containing all the info from config.ini.
     *
     * @var object
     */
    protected $_config;

    /**
     * Set the config array (config.ini contents) to private variable.
     *
     * @param object $config The config array.
     */
    public function __construct($config)
    {
        $this->_config = $config;

        parent::__construct();
    }

    /**
     * Will render a formelement and it's associated label.
     * If the element has an attribute fieldset defined, call renderFieldset, otherwise call renderGeneric.
     *
     * @param ElementInterface  $element    The element to render.
     * @return string                       The generated HTML string.
     */
    public function renderDecorated($element)
    {
        return !empty($element->getAttribute('fieldset')) ? $this->renderFieldset($element) : $this->renderGeneric($element);
    }

    /**
     * Generic formelement renderer. If called with $hidden: true, will hide the entire row (used for fieldsets).
     *
     * @param ElementInterface  $element    The Element to render.
     * @param bool              $hidden     Whether the entire form-group should be hidden on load.
     * @return string                       The generated HTML string.
     */
    protected function renderGeneric($element, $hidden = false)
    {
        $name = $element->getName();
        $hasErrors = $this->hasMessagesFor($name);

        if (get_class($element) == 'Phalcon\Forms\Element\Hidden')
        {
            $html = $element;
        }
        else
        {
            $class = $element->getAttribute('class');

            if (strpos($class, 'hidden') !== false)
            {
                $hidden = true;
                $element->setAttribute('class', str_replace('hidden', '', $class));
            }

            $html = '<div class="form-group row' . ($hasErrors ? ' has-error' : null) . ($hidden ? ' hidden' : null) . '">';
            $html .= '<div class="col-lg-3 col-sm-4 col-xs-12 text-right-not-xs">' . $element->label(['class' => 'text-bold']) . '</div>';
            $html .= '<div class="col-lg-4 col-sm-5 col-xs-12"><div class="input-group">' . $element;

            if (get_class($element) == 'Phalcon\Forms\Element\Password')
            {
                $html .= '<a class="btn btn-default input-group-addon toggle-password" title="Toggle password display"><i class="fa fa-eye"></i></a>';
            }
            else if (strpos($class, 'location') !== false)
            {
                $html .= '<a class="btn btn-default input-group-addon" title="Get location"><i class="fa fa-map-marker-alt"></i></a>';
            }

            $html .= '<a class="' . (get_class($element) != 'Phalcon\Forms\Element\Check' ? 'input-group-addon' : 'pull-right') . ' btn btn-default" data-fancybox data-type="iframe" title="Help" href="';
            $html .=  $this->_config->application->baseUri . 'settings/help/' . $name . '">';
            $html .= '<i class="fa fa-question"></i></a>';

            $html .= '</div></div><div class="col-lg-5 col-sm-3 col-xs-12">';

            if ($hasErrors)
            {
                $html .= '<div class="error pull-left">' . $this->getMessagesFor($name)[0]->getMessage() .'</div>';
            }

            $html .= '</div></div>';
        }

        return $html;
    }

    /**
     * Renders formelements that should be grouped in a fieldset.
     *
     * @param ElementInterface  $element    The element to render.
     * @return string                       The generated HTML string.
     */
    protected function renderFieldset($element)
    {
        $attributes = $element->getAttributes();
        $fieldset = $attributes['fieldset'];
        $name = $element->getName();
        $html = '';

        unset($attributes['fieldset']);
        $element->setAttributes($attributes);

        if ($fieldset === true)
        {
            $html = $this->renderGeneric($element, true);
        }
        else if ($fieldset == 'end')
        {
            $html = $this->renderGeneric($element, true) . '</fieldset>';
        }
        else
        {
            $html = '<fieldset><legend class="row"><div class="col-lg-3 col-sm-4 col-xs-10 text-right-not-xs">';
            $html .=  $element . '<span>'. $fieldset .  '</span>';
            $html .= '</div><div class="col-lg-4 col-sm-5 col-xs-2">';
            $html .= '<a class="fa fa-question pull-right btn btn-default" data-fancybox data-type="iframe" href="' . $this->_config->application->baseUri . 'settings/help/' . $name . '" title="help"></a>';
            $html .= '</div></legend>';
        }

        return $html;
    }
}
