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
    protected $formFieldClasses = [];

    /**
     * The configuration object containing all the info from config.ini.
     *
     * @var object
     */
    public $config;
    public $translator;

    /**
     * Set the config array (config.ini contents) to private variable.
     *
     * @param object $config The config array.
     */
    public function __construct($entity = null)
    {
        $this->config = $this->di->get('config');
        $this->translator = $this->di->get('translator');

        parent::__construct($entity);
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
            ob_start();
            $class = $element->getAttribute('class');

            if (strpos($class, 'hidden') !== false)
            {
                $hidden = true;
                $element->setAttribute('class', str_replace('hidden', '', $class));
            }

            require(APP_PATH . 'app/forms/views/element.phtml');
            $html = ob_get_clean();
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
            ob_start();
            require(APP_PATH . 'app/forms/views/fieldset.phtml');
            $html = ob_get_clean();
        }

        return $html;
    }

    public function hasHelp($name)
    {
        return isset($this->translator->helpContent[$name]);
    }

    protected function setFormFieldClasses($namespace)
    {
		$formFieldFiles = array_diff(scandir(APP_PATH . $this->config->application->formsDir . 'formfields/' . strtolower($namespace)), array('..', '.'));
		foreach ($formFieldFiles as $file)
        {
			$class =  'Chell\Forms\FormFields\\' . $namespace . '\\' . basename($file, '.php');
			$this->formFieldClasses[] = new $class;
        }

		foreach ($this->formFieldClasses as $class)
        {
			$class->setFields($this);
        }
    }

    public function getAllErrorMessagesForElement($name, $seperator = "\n")
    {
        $messages = $this->getMessagesFor($name);
        $errorMessages = '';
        foreach ($messages as $message)
        {
            $errorMessages .= $message->getMessage() . $seperator;
        }

        return $errorMessages;
    }
}
