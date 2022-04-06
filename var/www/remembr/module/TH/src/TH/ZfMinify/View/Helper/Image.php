<?php
/**
* @link http://github.com/dkcwd/dkcwd-zf2-munee for the canonical source repository
* @author Dave Clark dave@dkcwd.com.au
* @copyright (c) Dave Clark 2012 (https://www.dkcwd.com.au)
* @license http://opensource.org/licenses/mit-license.php
*/

namespace TH\ZfMinify\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Image extends AbstractHelper
{
    /**
	* View helper to format strings for making requests to the Munee Controller via the
	* custom route provided in this module.
	*
	* @param string $path A string representing the paths to an image file.
	* @param string $params A string representing the image resize query string.
	* @param array $attributes [optional] An array of key value pairs to be added as attributes, the default value is null.
	* @return A formatted string or null if invalid parameters are supplied.
	*/
    public function __invoke($path, $params, $attributes = null)
    {
        if (! is_string($path)) return;
        if (! is_string($params)) return;
        if (! is_null($attributes) && ! is_array($attributes)) return;

        $attributeString = '';

        if (is_array($attributes)) {

            foreach ($attributes as $key => $value) {
                if (! is_string($key) && ! is_string($value)) continue;
                $attributeString .= $key . '="' . $value . '" ';
            }
        }

		$src = $this->view->url('ThZfMinify') . '?' . http_build_query(array('files' => $path, 'resize' => $params));

        return '<img src="'.$src.'" ' . $attributeString . ' />';
    }
}