<?php

namespace Chell\Controllers;

use Chell\Models\SettingsContainer;
use Chell\Models\Users;
use Phalcon\Mvc\Controller;

/**
 * The baseController used by all controllers. Loads the config.ini to a variable.
 *
 * @package Controllers
 */
class BaseController extends Controller
{
    protected SettingsContainer $settings;

    private array $controllersToLoadMenu = ['index', 'about', 'settings'];

    /**
     * Sets the config object to $this->config and retrieves menuitems for controllers that requires it.
     */
    public function initialize()
    {
        $this->settings = $this->di->get('settings');

        if (in_array($this->dispatcher->getControllerName(), $this->controllersToLoadMenu))
        {
            if ($this->session->get('auth'))
            {
                $user = Users::findFirst([
                    'conditions' => 'id = ?1',
                    'bind'       => [1 => $this->session->get('auth')['id']],
                ]);
                $this->view->user = $user;
            }
        }

        $this->view->bgcolor = $this->getBackgroundColor();
    }

    /**
     * Retreives the background color class based on the settings.
     * If set to 'timebg', get the sunrise/sunset infor and compare this to the time in order to decide what color to use.
     *
     * @return string   The CSS class used to set the background color.
     */
    private function getBackgroundColor() : string
    {
        if ($this->settings->application->background == 'timebg')
        {
            $sunInfo = date_sun_info(time(), $this->settings->application->background_latitude, $this->settings->application->background_longitude);
            $currentTime = time();

            return $currentTime < $sunInfo['sunrise'] || $currentTime > $sunInfo['sunset'] ? 'darkbg' : 'lightbg';
        }

        return $this->settings->application->background;
    }

    /**
     * Writes ini file based on associative array.
     *
     * @param array     $assoc_arr      The array to write to the ini file.
     * @param string    $path           The path to write the ini file to.
     * @param bool      $has_sections   If the ini file has sections (in the form of [section])
     * @return bool                     If the write was successful
     */
    protected function writeIniFile(array $assoc_arr, string $path, bool $has_sections = false) : bool
    {
        $content = '';

        if ($has_sections)
        {
            foreach ($assoc_arr as $key => $elem)
            {
                $content .= '[' . $key . ']' . PHP_EOL;
                foreach ($elem as $key2=>$elem2)
                {
                    if (is_array($elem2))
                    {
                        $count = count($elem2);
                        for ($i = 0; $i < $count; $i++)
                        {
                            $content .= $key2 .'[] = "' . $elem2[$i] . '"' . PHP_EOL;
                        }
                    }
                    else if ($elem2 == '')
                    {
                        $content .= $key2 . ' = ""' . PHP_EOL;
                    }
                    else
                    {
                        $content .= $key2. ' = "' . $elem2 . '"' . PHP_EOL;
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
                        $content .= $key . '[] = "' . $elem[$i] . '"' . PHP_EOL;
                    }
                }
                else if ($elem == '')
                {
                    $content .= $key . ' = ""' . PHP_EOL;
                }
                else
                {
                    $content .= $key . ' = "' . $elem . '"' . PHP_EOL;
                }
            }
        }

        if (!$handle = fopen($path, 'w'))
        {
            return false;
        }

        $success = fwrite($handle, $content);
        fclose($handle);

        return $success !== false;
    }

    /**
     * Resize an image based on $sourcePath and writes it back to $resizedPath.
     *
     * @param string $sourcePath     The source image path to resize
     * @param string $resizedPath    The destination path to save the resized image in.
     * @param int $maxWidth          The maximum width of the resized image, defaults to 800.
     * @param int $maxHeight         The maximum height of the resized image, defaults to 2000.
     * @param int $imageQuality      The image quality used for the JPEG compression of the resized image, defaults to 70.
     * @return boolean               Whether or not resized succeeded.
     */
    protected function resizeImage(string $sourcePath, string $resizedPath, int $maxWidth = 800, int $maxHeight = 2000, int $imageQuality = 70) : bool
    {
        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($sourcePath);
        $source_gd_image = false;

        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $source_gd_image = imagecreatefromgif ($sourcePath);
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
     * @param int $currentPage      The current page requested.
     * @param int $totalPages       The total amount of pages in the dataset.
     * @param string $baseURI       The baseURI to create pagination URLs with.
     * @param mixed $paginator      The pagination object, defaults to null (which will make a new stdClass).
     * @return object               An object with all pagination data.
     */
    public function GetPaginator(int $currentPage, int $totalPages, string $baseURI, $paginator = null)
    {
        if ($paginator == null)
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

    /**
     * Adds javascript file to collection. Sets defaults for all JS files.
     *
     * @param string $collection     The collection to add the JS file to
     * @param string $file           The file to add to the collection
     */
    protected function addJs(string $collection, string $file)
    {
        $this->assets->collection($collection)->addJs($file, true, false, ['defer' => 'defer'], $this->settings->application->version, true);
    }
}
