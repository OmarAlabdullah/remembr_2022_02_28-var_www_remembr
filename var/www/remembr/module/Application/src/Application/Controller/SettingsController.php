<?php

namespace Application\Controller;

use Base\Controller\BaseController;
use Zend\View\Model\ViewModel;

class SettingsController extends BaseController
{

    public function checkAccess($action)
    {
        if ($this->params('format') != 'json')
        {
            return true;
        }

        $page = $this->params('page');
        if (!($page instanceof \Application\Entity\Page ))
        {
            throw new \Exception('profile-page not found', 404);
        }
        if (!\Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin))
        {
            throw new \Exception('admin rights required', 401);
        }
        return true;
    }

    public function indexAction()
    {
        return $this->getView();
    }

    public function errorAction()
    {
        return $this->getView();
    }

    public function adminsAction()
    {
        return $this->getView();
    }

    public function blockAction()
    {
        return $this->getView();
    }

    public function infoAction()
    {
        return $this->getView();
    }

    public function inviteAction()
    {
        return $this->getView();
    }

    public function inviteEmailAction()
    {
        return $this->getView();
    }

    public function inviteFacebookAction()
    {
        $view = $this->getView();

        $config = $this->getServiceLocator()->get('Config');
        $appId = $config['TH']['ZfUser']['hybridauth']['providers']['Facebook']['keys']['id'];
        $view->setVariable('appId', $appId);

        $baseurl = rtrim($this->url()->fromRoute('remembr/page', array('page' => ''), array('force_canonical'=>true), 0), "/");
        $view->setVariable('baseurl', $baseurl);

		return $view;
    }

    public function labelsAction()
    {
        return $this->getView();
    }

    public function privacyAction()
    {
        return $this->getView();
    }

    public function publishAction()
    {
        return $this->getView();
    }

    public function themesAction()
    {
        return $this->getView();
    }

    public function qrcodeAction()
    {
        return $this->getView()->setVariable('page', $this->params('page'));
    }

}
