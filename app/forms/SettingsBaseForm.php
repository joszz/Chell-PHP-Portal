<?php

namespace Chell\Forms;

use Exception;
use Chell\Models\SettingsContainer;
use Chell\Messages\TranslatorWrapper;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\ElementInterface;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Form;
use Phalcon\Mvc\Model;
use Phalcon\Url;

/**
 * The base form class used in SettingsGeneralForm and SettingsDashboardForm.
 *
 * @package Forms
 */
class SettingsBaseForm extends Form
{
    public SettingsContainer $settings;
    public TranslatorWrapper $translator;
    public Url $url;

    /**
     * Gets the settings and translator from DI.
     *
     * @param Model $entity    The Phalcon entity to populate the form with.
     */
    public function __construct(Model $entity = null)
    {
        $this->url = new Url();
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
    public function renderElement(ElementInterface $element) : string
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
    private function renderElementInternal(ElementInterface $element, bool $hidden = false) : string
    {
        $name = $element->getName();
        $hasErrors = $this->hasMessagesFor($name);

        if (is_a($element, Hidden::class))
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
    private function renderFieldset(ElementInterface $element) : string
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
    public function hasHelp(string $name) : bool
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
			(new $class($this))->setFields();
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

    /**
     * Fixes broken Phalcon checkbox behaviour.
     *
     * @param array $data           The posted data
     * @param mixed $entity         The entity/model to set the values for
     * @param mixed $whitelist
     * @return SettingsBaseForm     Returns this class
     */
    public function bind(array $data, $entity, $whitelist = null): SettingsBaseForm
    {
        parent::bind($data, $entity, $whitelist);

        foreach ($this->elements as $field => $element)
        {
            if (is_a($element, Check::class))
            {
                $entity->$field = $data[$field] ?? '0';
            }
        }

		return $this;
    }

    /**
     * Used by SettingsDashboardForm and SettingsGeneralForm to save the posted data to multiple settings entities.
     * Uses a convention for naming the elements; $category-$settingname
     *
     * @param array $data           The posted data
     * @return SettingsBaseForm     Returns this class
     */
    public function customBind(array $data) : SettingsBaseForm
    {
        $elements = $this->getElements();

        foreach ($elements as $field => $element)
        {
            $isArray = strpos($field, '[]') !== false;
            $field = str_replace('[]', '', $field);
            $category = substr($field, 0, $categoryEnd = strpos($field, '-'));
            $setting = substr($field, $categoryEnd + 1);

            if (empty($category))
            {
                throw new Exception('Settings category can not be determined from field "' . $field . '"');
            }

            //todo: not the best way of dealing with this
            if (is_a($element, Hidden::class))
            {
                continue;
            }

            if (is_a($element, Check::class))
            {
                $this->settings->$category->$setting = $data[$field] ?? '0';
            }
            else
            {
                $this->settings->$category->$setting = $isArray ? implode($data[$field], ',') : $data[$field];
            }
        }

		return $this;
    }
}
