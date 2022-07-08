<?php

namespace Chell\Forms\FormFields\General;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

/**
 * The formfields for the Apache plugin
 *
 * @package Formfields
 */
class ImageProxyFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
        $this->fields[] = $imageproxyEnabled = new Check('imageproxy-enabled');
        $imageproxyEnabled->setLabel('Enabled')
            ->setAttributes([
                'value' => '1',
                'checked' => $this->form->settings->imageproxy->enabled == '1' ? 'checked' : null,
                'data-toggle' => 'toggle',
                'data-onstyle' => 'success',
                'data-offstyle' => 'danger',
                'data-size' => 'small',
                'fieldset' => 'Imageproxy'
            ]);

        $this->fields[] = $imageproxyUrl = new Text('imageproxy-url');
        $imageproxyUrl->setLabel('URL')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
            ->setDefault($this->form->settings->imageproxy->url)
            ->addValidators([
                new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'imageproxy-enabled']),
                new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
            ]);
    }
}