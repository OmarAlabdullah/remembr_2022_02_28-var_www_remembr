<?php

namespace TH\ZfctwigTranslation\Extension;

use \Zend\ServiceManager\ServiceLocatorAwareInterface;
use \Zend\ServiceManager\ServiceLocatorInterface;

/*
 * (c)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class I18n extends \Twig_Extension
{
    protected $translator;

    public function setTranslator(\Zend\I18n\Translator\Translator $translator)
    {
        return $this->translator = $translator;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

	public function translate($message, $textDomain='default', $locale=null)
	{
		return	$this->translator->translate($message, $textDomain, $locale);
	}
	
	public function translatePlural($singular, $plural, $number, $textDomain='default', $locale=null)
	{
		return $this->translator->translatePlural($singular, $plural, $number, $textDomain, $locale);
	}
	
    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array(new \TH\ZfctwigTranslation\TokenParser\Trans());
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'trans' => new \Twig_Filter_Method($this, 'translate'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'th_zfctwig_i18n';
    }
}
