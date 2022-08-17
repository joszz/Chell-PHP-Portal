<?php

namespace Chell\Forms\FormFields;

abstract class FormFields
{
    protected $hasFieldset = false;
    protected $fields;

    public function __construct(protected $form)
    {
    }

    /**
     * Initializes fields
     */
    protected abstract function initializeFields();

    /**
     * Adds fields to the form.
     */
    public function setFields()
    {
        $this->initializeFields();

        if ($this->hasFieldset)
        {
            end($this->fields)->setAttribute('closefieldset', true);
        }
        foreach ($this->fields as $field)
        {
            if ($this->hasFieldset)
            {
                $field->setAttribute('hasfieldset', true);
            }
            $this->form->add($field);
        }
    }
}