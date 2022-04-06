<?php

namespace Cms\Controller;

use Base\Controller\BaseController;

class PageController extends BaseController
{

	protected $indexActionLoggedOut = true;
	protected $view;

	public function indexAction()
	{
			$slug = $this->params()->fromRoute('slug', 'home');
			$lang = $this->getLang();
			
			$this->view = $this->getView();

			if ($page = $this->getEm()
				->getRepository('Cms\Entity\CmsPage')
				->findOneBy(array('slug' => $slug, 'lang' => $lang, 'deleted'=>0), array('updateDate' => 'DESC')))
			{
				$title = $page->getTitle();
				$text = $page->getText();
                $metaDescription = $page->getMetaDescription();
				$this->setSlugTemplate($slug);
                
				$this->getEvent()->getApplication()->getServiceManager()->get('Twig_Environment')->addGlobal('slug', $slug);
				
				$this->view->setVariables(
					array(
						'title' => $title,
						'text' => $text,
						'lang' => $lang,
						'metaDescription' => $metaDescription,
					)
				);

				switch ($slug)
				{
					case 'home':

						$form = new \TH\ZfUser\Form\CreateAccountForm('signuphome');
						$form->remove('firstname');
						$form->getInputFilter()->remove('firstname');
						$form->remove('lastname');
						$form->getInputFilter()->remove('lastname');
						$form->setAttribute('action', '/account/signup/2');

						// add video
						switch ($lang)
							{
								case 'nl' :
                                case 'nl-be' :
									$this->view->setVariable('vid', '103513509');
									break;
								case 'en':
								// intentional fallthrough
								default :
									$this->view->setVariable('vid', '103512311');
									break;
							}

							$this->view->setVariable('form', $form);
							break;
			}
		}
		else
		{
			// page not found
			$this->layout('application/layout/layout');
			$this->view->setTemplate('/error/404');
		}

		return $this->view;
	}

    /**
     * Set layout and template for this slug or use default application layout.
     *
     * @param string $slug
     */
    protected function setSlugTemplate($slug)
    {
        $template = '/template/' . $slug . '.twig';

        $resolver = $this->getEvent()
                ->getApplication()
                ->getServiceManager()
                ->get('Zend\View\Resolver\TemplatePathStack');

        if ($resolver->resolve($template))
        {
//            $this->layout('layout/cms-page.twig');
            $this->layout('application/layout/layout');
            $this->view->setTemplate($template);
        }
        else
        {
            $this->layout('application/layout/layout');
        }
    }

}
