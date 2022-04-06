<?php

namespace TH\ZfMinify\View\Helper;

use stdClass;
use Zend\View\Helper\HeadScript as HeadScriptOriginal;

/**
* Class HeadScript
* @see ServiceLocatorAwareInterface
*/
class HeadScript extends HeadScriptOriginal
{
    protected $regKey = 'TH_ZfMinify_View_Helper_HeadScript';

	protected $validType = array(
		'text/javascript'  => 'text/javascript',
		'text/coffeescript' => 'text/coffeescript',
	);

    /**
     * Retrieve string representation
     *
     * @param  string|int $indent Amount of whitespaces or string to use for indention
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
            ? $this->getWhitespace($indent)
            : $this->getIndent();

        if ($this->view) {
            $useCdata = $this->view->plugin('doctype')->isXhtml() ? true : false;
        } else {
            $useCdata = $this->useCdata ? true : false;
        }

        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd   = ($useCdata) ? '//]]>' : '//-->';

        $items = array();
		$scripts = array();
		$deferred = array();
        $minified_deferred = array();
        $this->getContainer()->ksort();
        $all_defered = true;
		foreach ($this as $item)
		{
			if (isset($item->type)
				&& isset($this->validType[$item->type])
				&& empty($item->attributes['debug'])
				&& isset($item->attributes['src'])
				&& empty($item->attributes['conditional'])
				&& preg_match('#^(?!(https?:)?//).*\.(js|coffee)$#i', $item->attributes['src']))
			{
                if (empty($item->attributes['defer']) || $item->attributes['defer'] !== 'defer')
                    $scripts[] = $item->attributes['src'];
                else
                    $minified_deferred[] = $item->attributes['src'];
			}
			elseif (! empty($item->attributes['defer']) || (!isset($item->attributes['defer']) && isset($item->source)) )
			{
				$deferred[] = $item;
			}
			else
			{
                $this->processScript($items, $scripts, $minified_deferred, $deferred, $indent, $escapeStart, $escapeEnd);
				$items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd); // Add the item
                $all_defered = true;
			}
		}

		// Make sure we pick up the final minified item if it exists.
		$this->processScript($items, $scripts, $minified_deferred, $deferred, $indent, $escapeStart, $escapeEnd);

		return $indent . implode($this->escape($this->getSeparator()) . $indent, $items);
	}
    protected function getAbsoluteFilenames($filenames) {
        $absoluteFilenames = array();
        foreach ($filenames as $filename) {
            $count = 1;
            while ($count > 0) {
                $filename = preg_replace('%(/\\.\\.?|\\.\\.?/)%', '', $filename, -1, $count);
                if (substr($filename, 0, 1) != '/') $filename = '/' . $filename;
            }
            $filename = str_replace(SUB_FOLDER, '', $filename);
            $absoluteFilenames[] = WEBROOT . $filename;
        }
        return $absoluteFilenames;
    }
    
    protected function getMostRecentModification($files) {
        $most_recent = 0;
        $other_filenames = $this->getAbsoluteFilenames($files);
        foreach($other_filenames as $filename) {
            $most_recent = max($most_recent, filemtime($filename));
        }
        return $most_recent;
    }

    protected function processMinify(&$items, &$scripts, $attributes, $indent, $escapeStart, $escapeEnd) {
        $item = new stdClass();
        $item->type = 'text/javascript';
        $item->attributes['src'] = $this->view->url('ThZfMinify') . '?' . http_build_query(array('files' => implode(',', $scripts), 'minify' => 'true', 'last_update' => $this->getMostRecentModification($scripts)));
        foreach ($attributes as $attribute => $value)
            $item->attributes[$attribute] = $value;
        $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
        $scripts = array();   
    }
    
	protected function processScript(&$items, &$scripts, &$minified_deferred, &$deferred, $indent, $escapeStart, $escapeEnd)
	{
        if ($scripts) $this->processMinify($items, $scripts, array(), $indent, $escapeStart, $escapeEnd);
        if ($minified_deferred) $this->processMinify($items, $minified_deferred, ['defer' => 'defer'], $indent, $escapeStart, $escapeEnd);
		foreach($deferred as $item)
		{
			$items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
		}
		$deferred = array();
	}
}