<?php

namespace Chell\Controllers;

use Phalcon\Mvc\Controller;
use Chell\Models\Menus;

/**
 * The baseController used by all controllers. Loads the config.ini to a variable.
 *
 * @package Controllers
 */
class BaseController extends Controller
{
    protected $config;

    /**
     * Sets the config object to $this->config.
     *
     * @return  void
     */
    public function initialize()
    {
        $this->config = $this->di->get('config');

        $this->view->menu = Menus::findFirst(array(
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => array(1 => 1),
        ));
    }

    /**
     * Writes ini file based on associative array.
     *
     * @param array $assoc_arr          The array to write to the ini file.
     * @param string $path              The path to write the ini file to.
     * @param bool $has_sections        If the ini file has sections (in the form of [section])
     * @return bool                     If the write was succesful
     */
    protected function writeIniFile($assoc_arr, $path, $has_sections = false)
    {
        $content = "";

        if ($has_sections)
        {
            foreach ($assoc_arr as $key=>$elem)
            {
                $content .= "[".$key."]\n";
                foreach ($elem as $key2=>$elem2)
                {
                    if(is_array($elem2))
                    {
                        for($i=0;$i<count($elem2);$i++)
                        {
                            $content .= $key2."[] = \"".$elem2[$i]."\"\n";
                        }
                    }
                    else if($elem2=="") $content .= $key2." = \n";
                    else $content .= $key2." = \"".$elem2."\"\n";
                }
            }
        }
        else
        {
            foreach ($assoc_arr as $key=>$elem)
            {
                if(is_array($elem))
                {
                    for($i=0;$i<count($elem);$i++)
                    {
                        $content .= $key."[] = \"".$elem[$i]."\"\n";
                    }
                }
                else if($elem=="") $content .= $key." = \n";
                else $content .= $key." = \"".$elem."\"\n";
            }
        }

        if (!$handle = fopen($path, 'w'))
        {
            return false;
        }

        $success = fwrite($handle, $content);
        fclose($handle);

        return $success;
    }
}
