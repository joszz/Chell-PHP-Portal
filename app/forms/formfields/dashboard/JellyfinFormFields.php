<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Chell\Models\Jellyfin;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class JellyfinFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$jellyfinEnabled = new Check('jellyfin-enabled');
		$jellyfinEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->config->jellyfin->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Jellyfin'
		]);

        $jellyfinUrl = new Text('jellyfin-url');
		$jellyfinUrl->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->config->jellyfin->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'jellyfin-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$jellyfinToken = new Password('jellyfin-token');
		$jellyfinToken->setLabel('Token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->jellyfin->token)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'jellyfin-enabled']));

        $jellyfinUserId = new Text('jellyfin-userid');
		$jellyfinUserId->setLabel('User id')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->jellyfin->userId)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'jellyfin-enabled']));

        $jellyfinUserId = new Text('jellyfin-userid');
		$jellyfinUserId->setLabel('User id')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->jellyfin->userId)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'jellyfin-enabled']));

		$jellyfinRotateInterval = new Numeric('jellyfin-rotate-interval');
		$jellyfinRotateInterval->setLabel('Rotate interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->jellyfin->rotateInterval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'jellyfin-enabled'])
		]);

		$jellyfinViews = new Select('jellyfin-views[]');
		$jellyfinViews->setLabel('Libraries')
			->setFilters(['striptags', 'string'])
			->setAttributes([
				'class' => 'form-control',
				'multiple' => 'multiple',
				'fieldset' => 'end',
				'data-selected' => $form->config->jellyfin->views,
				'data-apiurl' => '../jellyfin/views'
			])
			->setUserOptions(['buttons' => ['refresh_api_data']]);

		$form->add($jellyfinEnabled);
		$form->add($jellyfinUrl);
		$form->add($jellyfinToken);
		$form->add($jellyfinUserId);
		$form->add($jellyfinRotateInterval);
		$form->add($jellyfinViews);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->jellyfin->enabled = isset($data['jellyfin-enabled']) && $data['jellyfin-enabled'] == 'on' ? '1' : '0';
        $config->jellyfin->url = $data['jellyfin-url'];
        $config->jellyfin->token = $data['jellyfin-token'];
        $config->jellyfin->userId = $data['jellyfin-userid'];
		$config->jellyfin->rotateInterval = $data['jellyfin-rotate-interval'];
		$config->jellyfin->views = implode($data['jellyfin-views'], ',');
    }
}