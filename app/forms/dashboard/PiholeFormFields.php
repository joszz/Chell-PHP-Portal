<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;

class PiholeFormFields implements IDashboardFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$piholeEnabled = new Check('pihole-enabled');
		$piholeEnabled->setLabel('Enabled');
		$piholeEnabled->setAttributes([
			'checked' => $form->_config->pihole->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Pi-hole'
		]);

		$piholeURL = new Text('pihole-url');
		$piholeURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->pihole->URL);

		$form->add($piholeEnabled);
		$form->add($piholeURL);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->pihole->enabled = isset($data['pihole-enabled']) && $data['pihole-enabled'] == 'on' ? '1' : '0';
        $config->pihole->URL = $data['pihole-url'];
    }
}