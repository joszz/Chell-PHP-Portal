<?php

namespace Chell\Forms;

use Chell\Forms\Validators\Mac;
use Chell\Models\Devices;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\Ip;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Url as UrlValidator;

/**
 * The form responsible for adding new devices.
 *
 * @package Forms
 */
class SettingsDeviceForm extends SettingsBaseForm
{
    /**
     * Add all fields to the form and set form specific attributes.
     */
    public function initialize(Devices $entity)
    {
        $name = new Text('name');
        $name->setLabel('Name')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->addValidator(new PresenceOf(['message' => $this->translator->validation['required']]));

        $ip = new Text('ip');
        $ip->setLabel('IP')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->addValidators([
                new PresenceOf(['message' => $this->translator->validation['required']]),
                new IP(['message' => $this->translator->validation['ip'], 'allowPrivate' => true])
            ]);

        $mac = new Text('mac');
        $mac->setLabel('MAC')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->addValidator(new Mac(['message' => $this->translator->validation['mac'], 'allowEmpty' => true]));

        $shutdownUser = new Text('shutdown_user');
        $shutdownUser->setLabel('Shutdown user')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-user']);

        $shutdownPassword = new Password('shutdown_password');
        $shutdownPassword->setLabel('Shutdown password')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password']);

        $shutdownMethod = new Select(
            'shutdown_method',
            ['none' => 'None', 'rpc' => 'RPC', 'adb' => 'ADB'],
            ['useEmpty' => false]
        );
        $shutdownMethod->setLabel('Shutdown method');

        $showDasboard = new Check('show_on_dashboard');
        $showDasboard->setLabel('Show on dashboard')
            ->setAttributes([
                'value' => '1',
                'checked' => isset($entity->show_on_dashboard) && $entity->show_on_dashboard == '1' ? 'checked' : null,
                'data-toggle' => 'toggle',
                'data-onstyle' => 'success',
                'data-offstyle' => 'danger',
                'data-size' => 'small'
        ]);

        $hypervadminEnabled = new Check('hypervadmin_enabled');
		$hypervadminEnabled->setLabel('HyperVAdmin enabled')
			->setAttributes([
				'value' => '1',
                'checked' => !empty($entity->hypervadmin_url) ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'HyperVAdmin enabled'
		]);

        $hypervadminUrl = new Text('hypervadmin_url');
        $hypervadminUrl->setLabel('HyperVAdmin URL')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => true])
            ->addValidator(new UrlValidator(['message' => $this->translator->validation['url'], 'allowEmpty' => true]));

        $hypervadminUsername = new Text('hypervadmin_user');
        $hypervadminUsername->setLabel('HyperVAdmin username')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => true]);

        $hypervadminPassword = new Password('hypervadmin_password');
        $hypervadminPassword->setLabel('HyperVAdmin password')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => 'end']);

        $this->add($name);
        $this->add($ip);
        $this->add($mac);
        $this->add($shutdownUser);
        $this->add($shutdownPassword);
        $this->add($shutdownMethod);
        $this->add($showDasboard);
        $this->add($hypervadminEnabled);
        $this->add($hypervadminUrl);
        $this->add($hypervadminUrl);
        $this->add($hypervadminUsername);
        $this->add($hypervadminPassword);
    }
}