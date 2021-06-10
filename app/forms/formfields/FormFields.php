<?php

namespace Chell\Forms\FormFields;

abstract class FormFields
{
    protected $form;
    protected $fields;

    public function __construct($form)
    {
        $this->form = $form;
    }

    /**
     * Initializes fields
     */
    protected abstract function initializeFields();

    /**
     * Adds fields to the form.
     */
    public function setFields(){
        $this->initializeFields();

        foreach($this->fields as $field)
        {
            $this->form->add($field);
        }
    }
}