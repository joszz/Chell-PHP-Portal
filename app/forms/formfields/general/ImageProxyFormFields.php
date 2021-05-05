<?php

namespace Chell\Forms\FormFields\General;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Url as UrlValidator;

class ImageProxyFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
        $imageproxyEnabled = new Check('imageproxy-enabled');
        $imageproxyEnabled->setLabel('Enabled')
            ->setAttributes([
                'checked' => $form->config->imageproxy->enabled == '1' ? 'checked' : null,
                'data-toggle' => 'toggle',
                'data-onstyle' => 'success',
                'data-offstyle' => 'danger',
                'data-size' => 'small',
                'fieldset' => 'Imageproxy'
            ]);

        $imageproxyUrl = new Text('imageproxy-url');
        $imageproxyUrl->setLabel('URL')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => true])
            ->setDefault($form->config->imageproxy->URL)
            ->addValidators([
                new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'imageproxy-enabled']),
                new UrlValidator(['message' => $form->translator->validation['url']])
            ]);

        $form->add($imageproxyEnabled);
        $form->add($imageproxyUrl);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->imageproxy->enabled = isset($data['imageproxy-enabled']) && $data['imageproxy-enabled'] == 'on' ? '1' : '0';
        $config->imageproxy->URL = $data['imageproxy-url'];
    }
}