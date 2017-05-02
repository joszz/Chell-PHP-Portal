<?php

namespace Chell\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\Regex;

/**
 * The form responsible for the dashboard settings.
 *
 * @package Forms
 */
class SettingsDashboardRCPUForm extends Form
{
    /**
     * The configuration object containing all the info from config.ini.
     * @var array
     */
    private $_config;

    /**
     * Set the config array (config.ini contents) to private variable.
     *
     * @param array $config     The config array.
     */
    public function __construct($config)
    {
        $this->_config = $config;
        parent::__construct();
    }

    /**
     * Add all fields to the form and set form specific attributes.
     */
    public function initialize()
    {
        $rCpuURL = new Text('rcpu-url');
        $rCpuURL->setLabel('rCPU URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->rCpuURL);
    }

    /**
     * Check if form is valid. If so set the values to the config array.
     *
     * @param   array     $data     The form data posted.
     * @param   object    $entity   The entity to validate.
     * @return  bool                Whether or not form is valid.
     */
    public function IsValid($data = null, $entity = null)
    {
        $valid = parent::IsValid($data, $entity);

        if($valid)
        {

        }

        return $valid;
    }
}
