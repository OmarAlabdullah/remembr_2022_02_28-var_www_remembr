<?php

namespace Cms\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Exception;

class Cms extends AbstractHelper
{
	protected $page = null;
	protected $em = null;
	protected $defaultlang = '';

	public function __construct(\Doctrine\ORM\EntityManagerInterface $em, $defaultlang) {
		$this->em = $em;
        if ($defaultlang == 'nl_BE')
            $this->defaultlang = 'nl-be';
        else
            $this->defaultlang = \substr($defaultlang,0,2);
	}

    public function __invoke($slug, $lang = null)
    {
		if (empty($lang))
		{
			$lang = $this->defaultlang;
        }

		$this->page = $this->em->getRepository('Cms\Entity\CmsPage')->findOneBy(array('slug' => $slug, 'lang' => $lang, 'deleted' => false));

        $this->slug = $slug;
        $this->lang = $lang;
		return $this;
    }

	public function title()
	{
		if ($this->page && $this->page instanceof \Cms\Entity\CmsPage)
		{
			return $this->page->getTitle();
		}
		return '';
	}
    
    private function isAdmin() {
        $auth = new \Zend\Authentication\AuthenticationService();
        return (
            $auth->hasIdentity() && (
                count(\Auth\Rights\RightList::fromDoctrine(
                    $this->em->getRepository('Auth\Entity\UserRight')
                        ->findBy(array(
                            'user' => $auth->getIdentity()['userId'],
                            'path' => '/*',
                            'value' => -1
                        ))
                )) > 0 ||
                count(\Auth\Rights\RightList::fromDoctrine(
                    $this->em->getRepository('Auth\Entity\UserRight')
                        ->findBy(array(
                            'user' => $auth->getIdentity()['userId'],
                            'path' => '/*',
                            'value' => 7
                        ))
                )) > 0
        ));
    }

	public function text()
	{
        $prefix  = ''; $postfix = '';
        if ($this->isAdmin())
        {
            $prefix = '<div class="cms-editable"><a target="_blank" class="rm-cms-edit" href="/admin/cms/goto?slug=' . $this->slug . '&lang=' . $this->lang . '"></a>';
            $postfix = '</div>';
        }
        
		if ($this->page && $this->page instanceof \Cms\Entity\CmsPage)
		{
			return $prefix . $this->page->getText() . $postfix;
		}
		return $prefix . $postfix;
	}
}