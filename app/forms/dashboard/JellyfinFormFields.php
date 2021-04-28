<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;

class JellyfinFormFields implements IDashboardFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$jellyfinEnabled = new Check('jellyfin-enabled');
		$jellyfinEnabled->setLabel('Enabled');
		$jellyfinEnabled->setAttributes([
			'checked' => $form->_config->jellyfin->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->_config->jellyfin->url);

		$jellyfinToken = new Text('jellyfin-token');
		$jellyfinToken->setLabel('Token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->jellyfin->token);

        $jellyfinUserId = new Text('jellyfin-userid');
		$jellyfinUserId->setLabel('User id')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->jellyfin->userId);

		$form->add($jellyfinEnabled);
		$form->add($jellyfinUrl);
		$form->add($jellyfinToken);
		$form->add($jellyfinUserId);
	}

    public function setPostData(&$config, $data)
    {
        $config->jellyfin->enabled = isset($data['jellyfin-enabled']) && $data['jellyfin-enabled'] == 'on' ? '1' : '0';
        $config->jellyfin->url = $data['jellyfin-url'];
        $config->jellyfin->token = $data['jellyfin-token'];
        $config->jellyfin->userId = $data['jellyfin-userid'];
    }
}