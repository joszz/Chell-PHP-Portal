<?php

namespace Chell\Forms;

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
     * @var array
     */
    protected $_config;

    /**
     * Set the config array (config.ini contents) to private variable.
     *
     * @param array $config     The config array.
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
     * @param Element $element The element to render.
     * @return string          The generated HTML string.
     */
    public function renderDecorated($element)
    {
        $html = '';

        if(!empty($element->getAttribute('fieldset')))
        {
            $html .= $this->renderFieldset($element);
        }
        else
        {
            $html .= $this->renderGeneric($element);
        }

        return $html;
    }

    /**
     * Generic formelement renderer. If called with $hidden: true, will hide the entire row (used for fieldsets).
     * 
     * @param Element $element  The Element to render.
     * @param Bool $hidden      Whether the entire form-group should be hidden on load.
     * @return string           The generated HTML string.
     */
    protected function renderGeneric($element, $hidden = false)
    {
        $name = $element->getName();
        $hasErrors = $this->hasMessagesFor($name);
        $html = '';

        $html .= '<div class="form-group row' . ($hasErrors ? ' has-error' : null) . ($hidden ? ' hidden' : null) . '">';
        $html .= '<div class="col-lg-3 col-sm-4 col-xs-12 text-right-not-xs">' . $element->label(array('class' => 'text-bold')) . '</div>';
        $html .= '<div class="col-lg-4 col-sm-5 col-xs-12">' . $element . '</div>';
        $html .= '<div class="col-lg-5 col-sm-3 col-xs-12 error">';

        if($hasErrors)
        {
            $html .= $this->getMessagesFor($name)[0]->getMessage();
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Renders formelements that should be grouped in a fieldset.
     * @param Element $element  The element to render.
     * @return string           The generated HTML string.
     */
    protected function renderFieldset($element)
    {
        $attributes = $element->getAttributes();
        $fieldset = $attributes['fieldset'];
        $html = '';

        unset($attributes['fieldset']);
        $element->setAttributes($attributes);

        if($fieldset === true){
            $html = $this->renderGeneric($element, true);
        }
        else if($fieldset == 'end'){
            $html = $this->renderGeneric($element, true) . '</fieldset>';
        }
        else {
            $html = '<fieldset><legend>' . $fieldset . '</legend>' . $this->renderGeneric($element);
        }

        return $html;
    }
}
