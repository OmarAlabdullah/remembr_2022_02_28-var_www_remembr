<?php


namespace TH\ZfMinify\View\Helper;

use stdClass;
use Zend\View\Helper\HeadLink as HeadLinkOriginal;

/**
* Class HeadLink
*/
class HeadLink extends HeadLinkOriginal
{
    protected $regKey = 'TH_ZfMinify_View_Helper_HeadLink';

	protected $validType = array(
		'text/css'  => 'text/css',
		'text/less' => 'text/less',
		'text/scss' => 'text/scss'
	);

	/**
     * Render link elements as string
     *
     * @param  string|int $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent) ? $this->getWhitespace($indent) : $this->getIndent();

		$items = array();
		$stylesheets = array();
		$this->getContainer()->ksort();
		foreach ($this as $item)
		{
            if (isset($item->type)
				&& isset($this->validType[$item->type])
				&& empty($item->extras['debug'])
				&& ($item->conditionalStylesheet === false || $item->conditionalStylesheet === null)
				&& preg_match('#^(?!(https?:)?//).*\.(?:s?c|le)ss$#i', $item->href))
			{
				$stylesheets [$item->media][] = $item->href;
			}
			else
			{
				$this->processStylesheet($items, $stylesheets);
				$items [] = $this->itemToString($item); // Add the item
			}
		}

		// Make sure we pick up the final minified item if it exists.
		$this->processStylesheet($items, $stylesheets);

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
            //error_log(var_export(array($filename, filemtime($filename)), true));
            $most_recent = max($most_recent, filemtime($filename));
        }
        return $most_recent;
    }

	protected function processStylesheet(&$items, &$stylesheets)
	{
        foreach ($stylesheets as $media => $styles)
		{
			$minStyles = new stdClass();
			$minStyles->rel = 'stylesheet';
			$minStyles->type = 'text/css';
			$minStyles->href = $this->view->url('ThZfMinify') . '?' . http_build_query(array(
                'files' => implode(',', $styles),
                'minify' => 'true',
                'last_update' => '' . $this->getMostRecentModification($styles)
            ));
			$minStyles->media = $media;
			$minStyles->conditionalStylesheet = false;
			$items [] = $this->itemToString($minStyles); // add the minified item
		}
		$stylesheets = array(); // Empty our stylesheets array
	}
}