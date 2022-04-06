<?php

namespace Application\Controller;

use Base\Controller\BaseController;

class SitemapController extends BaseController
{
	public function indexAction()
	{
        $query_string = "SELECT p FROM Application\Entity\Page p "
				. "WHERE p.dateofdeath IS NOT NULL "
				. "AND p.status = 'published' "
				. "AND p.dateofdeath < CURRENT_DATE()";
        $query = $this->getEm()->createQuery($query_string);

        $pages = $query->getResult();
        $view = $this->getView();
		$view->setTerminal(true);
        $view->setVariable('pages', $pages);

		$this->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'text/xml; charset=utf-8');

        return $view;
	}
}
