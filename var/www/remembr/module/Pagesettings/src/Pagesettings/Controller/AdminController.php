<?php

namespace Pagesettings\Controller;

use Base\Controller\BaseController;

class AdminController extends BaseController
{

    protected $view;

    public function indexAction()
    {
        $this->view = $this->getView();

        $action = '';
        $params = $this->params()->fromRoute();
        if (isset($params['id']))
        {
            $action = $params['id'];
        }

        $pages = $this->getPages($action);

        $this->view->setVariables(
                array(
                    'pages' => $pages
                )
        );

        return $this->view;
    }

    // important
    public function setRequest($req)
    {
        $this->request = $req;
    }

    private function getPages($action = '')
    {
        $query_string = "SELECT p FROM Application\Entity\Page p";

        switch ($action)
        {
            case 'nip':
                $query_string .= " WHERE p.type = 'nip'";
                break;

            case 'vip':
                $query_string .= " WHERE p.type = 'vip'";
                break;

            case 'animal':
                $query_string .= " WHERE p.type = 'animal'";
                break;

            case 'rotators':
                $query_string .= " WHERE p.rotating= 1";
                break;

             case 'all':
                break;

            default:
                $query_string .= " WHERE p.type IS NULL";
                $query_string .= " OR p.type = ''";
                break;
        }

        $query_string .= " ORDER BY p.creationdate DESC";

        $query = $this->getEm()->createQuery($query_string);
        $pages = $query->getResult();

        return $this->getAbbrPages($pages);
    }

    protected function getAbbrPages($pages)
    {
        $abbrpagearr = array();
        foreach ($pages as $page)
        {
            $abbrpagearr[] = array(
                'id' => $page->getId(),
                'url' => $page->getUrl(),
                'firstname' => $page->getFirstname(),
                'lastname' => $page->getLastname(),
                'creationdate' => $page->getCreationDate(),
                'type' => $page->getType(),
                'rotating' => $page->getRotating(),
                'photo' => array('url' => $page->getPhoto() ? : '/images/user-icon-large.png'),
                'status' =>  $page->getStatus(),
            );
        }

        return $abbrpagearr;
    }

    public function saveTypeAction()
    {
        $request = $this->getRequest();
        if ($request->isPost())
        {
            $post = $request->getPost();
            $pagessettings= $post->pagesettings;

            foreach ($pagessettings as $id => $settings)
            {
                if ($editpage = $this->getEm()->find('Application\Entity\Page', $id))   // intended
                {
                    $editpage->setType($settings['type']);
                    $editpage->setRotating($settings['rotating']);
                }

            }
            $this->getEm()->flush();
        }

        return $this->redirect()->toUrl('/admin/pagesettings');
    }

}
