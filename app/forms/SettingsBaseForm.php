<?php

namespace Chell\Forms;

use Chell\Models\SettingsContainer;
use Chell\Messages\TranslatorWrapper;
use Phalcon\Forms\Element\ElementInterface;
use Phalcon\Forms\Form;
use Phalcon\Mvc\Model;

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
    public SettingsContainer $settings;
    public TranslatorWrapper $translator;

    /**
     * Set the config array (config.ini contents) to private variable.
     *
     * @param Model $entity    The Phalcon entity to populate the form with.
     */
    public function __construct(Model $entity = null)
    {
        $this->settings = $this->di->get('settings');
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
    public function renderElement(ElementInterface $element)
    {
        return !empty($element->getAttribute('fieldset')) ? $this->renderFieldset($element) : $this->renderElementInternal($element);
    }

    /**
     * Generic formelement renderer. If called with $hidden: true, will hide the entire row (used for fieldsets).
     *
     * @param ElementInterface  $element    The Element to render.
     * @param bool              $hidden     Whether the entire form-group should be hidden on load.
     * @return string                       The generated HTML string.
     */
    private function renderElementInternal(ElementInterface $element, bool $hidden = false)
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

            require APP_PATH . 'app/views/forms/element.phtml';
            $html = ob_get_clean();
        }

        return $html;
    }

    /**
     * Renders the form
     *
     * @param string $id    The HTML id attribute to set on the form.
     */
    public function renderForm(string $id)
    {
        require APP_PATH . 'app/views/forms/form.phtml';
    }

    /**
     * Summary of renderButton
     *
     * @param string $button
     * @param string $name
     * @param ElementInterface  $element    The element to render ther button for.
     * @param mixed $element
     */
    public function renderButton(string $button, string $name = '', ElementInterface $element = null)
    {
        require APP_PATH . 'app/views/forms/buttons/' . $button . '.phtml';
    }

    /**
     * Renders formelements that should be grouped in a fieldset.
     *
     * @param ElementInterface  $element    The element to render.
     * @return string                       The generated HTML string.
     */
    private function renderFieldset(ElementInterface $element)
    {
        $attributes = $element->getAttributes();
        $fieldset = $attributes['fieldset'];
        $name = $element->getName();
        $html = '';

        unset($attributes['fieldset']);
        $element->setAttributes($attributes);

        if ($fieldset === true)
        {
            $html = $this->renderElementInternal($element, true);
        }
        else if ($fieldset == 'end')
        {
            $html = $this->renderElementInternal($element, true) . '</fieldset>';
        }
        else
        {
            ob_start();
            require APP_PATH . 'app/views/forms/fieldset.phtml';
            $html = ob_get_clean();
        }

        return $html;
    }

    /**
     * Whether the element with $name has any help content.
     *
     * @param string $name  The name of the formelement.
     * @return bool         Has help?
     */
    public function hasHelp(string $name)
    {
        return isset($this->translator->helpContent[$name]);
    }

    /**
     * Creates all the FormField classes in the given namespace.
     *
     * @param string $namespace     Which FormFields namespace to create FormField instances for.
     */
    protected function setFormFieldClasses(string $namespace)
    {
		$formFieldFiles = array_diff(scandir(APP_PATH . 'app/forms/formfields/' . strtolower($namespace)), array('..', '.'));
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

    /**
     * Retrieves all error messages of a form element with the given name.
     *
     * @param string $name       The form element's name
     * @param string $seperator  The seperator to use to create the string. Defaults to newline.
     * @return string            All the error messages concatenated, using the seperator.
     */
    public function getAllErrorMessagesForElement(string $name, string $seperator = "\n") : string
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
