<?php

namespace Application\Controller;

use Base\Controller\BaseController;

class LandingController extends BaseController
{

    public function checkAccess($action)
    {
        return $this->params('format') == 'tpl';
    }

    public function videoAction()
    {
        $lang = $this->getLang();
        $view = $this->getView();
		switch ($lang)
		{
            case 'nl' :
            case 'nl-be' :
                $view->setVariable('vid', '103513509');
                break;
            case 'en':
            // intentional fallthrough
            default :
                $view->setVariable('vid', '103512311');
                break;
        }
        return $view;
    }
}
