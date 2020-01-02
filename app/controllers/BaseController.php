<?php

namespace Chell\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Debug\Dump;

use Chell\Models\Menus;

/**
 * The baseController used by all controllers. Loads the config.ini to a variable.
 *
 * @package Controllers
 */
class BaseController extends Controller
{
    protected $config;

    private $controllersToLoadMenu = array('index', 'about', 'settings');

    /**
     * Sets the config object to $this->config and retrieves menuitems for controllers that requires it.
     */
    public function initialize()
    {
        $this->config = $this->di->get('config');

        if(in_array($this->dispatcher->getControllerName(), $this->controllersToLoadMenu))
        {
            $this->view->menu = Menus::findFirst(array(
                'conditions' => 'id = ?1',
                'order'      => 'name',
                'bind'       => array(1 => 1),
            ));
        }
    }

    /**
     * Wrapper for Phalcon Debug.
     *
     * @param mixed $dump   The variable to dump.
     * @return string       The dumped variable as string.
     */
    public function dump($dump)
    {
        return (new Dump())->variable($dump);
    }

    /**
     * Writes ini file based on associative array.
     *
     * @param array     $assoc_arr      The array to write to the ini file.
     * @param string    $path           The path to write the ini file to.
     * @param bool      $has_sections   If the ini file has sections (in the form of [section])
     * @return bool                     If the write was successful
     */
    protected function writeIniFile($assoc_arr, $path, $has_sections = false)
    {
        $content = '';

        if ($has_sections)
        {
            foreach ($assoc_arr as $key => $elem)
            {
                $content .= '[' . $key . "]\n";
                foreach ($elem as $key2=>$elem2)
                {
                    if (is_array($elem2))
                    {
                        $count = count($elem2);
                        for ($i = 0; $i < $count; $i++)
                        {
                            $content .= $key2 .'[] = "' . $elem2[$i] . "\"\n";
                        }
                    }
                    else if ($elem2 == '')
                    {
                        $content .= $key2 . " = \"\"\n";
                    }
                    else
                    {
                        $content .= $key2. ' = "' . $elem2 . "\"\n";
                    }
                }
            }
        }
        else
        {
            foreach ($assoc_arr as $key => $elem)
            {
                if (is_array($elem))
                {
                    $count = count($elem);
                    for ($i = 0; $i < $count; $i++)
                    {
                        $content .= $key . '[] = "' . $elem[$i] . "\"\n";
                    }
                }
                else if ($elem == '')
                {
                    $content .= $key . " = \"\"\n";
                }
                else
                {
                    $content .= $key . ' = "' . $elem . "\"\n";
                }
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

    /**
     * Resize an image based on $sourcePath and writes it back to $resizedPath.
     *
     * @param mixed $sourcePath     The source image path to resize
     * @param mixed $resizedPath    The destination path to save the resized image in.
     * @param mixed $maxWidth       The maximum width of the resized image, defaults to 800.
     * @param mixed $maxHeight      The maximum height of the resized image, defaults to 2000.
     * @param mixed $imageQuality   The image quality used for the JPEG compression of the resized image, defaults to 70.
     * @return boolean              Whether or not resized succeeded.
     */
    protected function resizeImage($sourcePath, $resizedPath, $maxWidth = 800, $maxHeight = 2000, $imageQuality = 70)
    {
        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($sourcePath);
        $source_gd_image = false;

        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $source_gd_image = imagecreatefromgif($sourcePath);
                break;

            case IMAGETYPE_JPEG:
                $source_gd_image = imagecreatefromjpeg($sourcePath);
                break;

            case IMAGETYPE_PNG:
                $source_gd_image = imagecreatefrompng($sourcePath);
                break;
        }

        if ($source_gd_image === false)
        {
            return false;
        }

        $source_aspect_ratio = $source_image_width / $source_image_height;
        $thumbnail_aspect_ratio = $maxWidth / $maxHeight;

        if ($source_image_width <= $maxWidth && $source_image_height <= $maxHeight)
        {
            $thumbnail_image_width = $source_image_width;
            $thumbnail_image_height = $source_image_height;
        }
        elseif ($thumbnail_aspect_ratio > $source_aspect_ratio)
        {
            $thumbnail_image_width = (int) ($maxHeight * $source_aspect_ratio);
            $thumbnail_image_height = $maxHeight;
        }
        else
        {
            $thumbnail_image_width = $maxWidth;
            $thumbnail_image_height = (int) ($maxWidth / $source_aspect_ratio);
        }

        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
        imagejpeg($thumbnail_gd_image, $resizedPath, $imageQuality);
        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);

        return true;
    }

    /**
     * Does some basic math to calculate the different values used for building paginators.
     *
     * @param mixed $currentPage    The current page requested.
     * @param mixed $totalPages     The total amount of pages in the dataset.
     * @param mixed $baseURI        The baseURI to create pagination URLs with.
     * @param mixed $paginator      The pagination object, defaults to null (which will make a new stdClass).
     * @return object   an object with all pagination data.
     */
    public function GetPaginator($currentPage, $totalPages, $baseURI, $paginator = null)
    {
        if($paginator == null)
        {
            $paginator = new \stdClass();
        }

        $paginator->baseURI = $baseURI;
        $paginator->current = $currentPage;
        $paginator->total_items = $totalPages;
        $paginator->start = 1;
        $paginator->end = 9;

        if ($currentPage - 5 > 0)
        {
            $paginator->start = $currentPage - 5;
            $paginator->end = $currentPage + 5;
        }

        if ($paginator->total_items < $paginator->end)
        {
            $paginator->end = $paginator->total_items;
        }

        return $paginator;
    }
}
