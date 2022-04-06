<?php

namespace Application\Controller;

use Base\Controller\BaseController;

class SearchController extends BaseController
{

    public function checkAccess($action)
    {
        switch ($action)
        {
            case 'index' :// purely template
                if ($this->params('format') == 'json')
                {
                    throw new \Exception('format not available', 400);
                }
                return true;
            case 'extended':
            case 'get':
            case 'recent':
            case 'random':
            case 'rotators':
                if ($this->params('format') !== 'json')
                {
                    throw new \Exception('format not available', 400);
                }
                return true;
        }

        return false;
    }

    public function indexAction()
    {
        return $this->getView();
    }

    /**
     * Extended search for: firstname, lastname, dateofbirth, dateofdeath
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function extendedAction()
    {
        $req = $this->getRequest();
        if ($req->isPost())
        {
            $data = json_decode($req->getContent(), TRUE) ? : $req->getPost();

            $query_string = "SELECT p FROM Application\Entity\Page p WHERE p.dateofdeath IS NOT NULL ";
            $params = array();

            if (!empty($data['firstname']))
            {
                $query_string .= " AND p.firstname LIKE CONCAT('%',:firstname,'%') ";
                $params['firstname'] = $data['firstname'];
            }
            if (!empty($data['lastname']))
            {
                $query_string .= " AND p.lastname LIKE CONCAT('%',:lastname,'%') ";
                $params['lastname'] = $data['lastname'];
            }

            if (!empty($data['dateofbirth']))
            {
                $query_string .= " AND p.dateofbirth LIKE CONCAT('%',:dateofbirth,'%') ";
                $params['dateofbirth'] = $data['dateofbirth'];
            }

            if (!empty($data['dateofdeath']))
            {
                $query_string .= " AND p.dateofdeath LIKE CONCAT('%',:dateofdeath,'%') ";
                $params['dateofdeath'] = $data['dateofdeath'];
            }

            // if both checkboxes are checked, ignore the privacy settings (design suggest checkboxes instead of radio buttons)
            if (!empty($data['open']) && empty($data['private']))
            {
                $query_string .= " AND (p.private IS NULL OR p.private = '0') ";
            }

            if (!empty($data['private']) && empty($data['open']))
            {
                $query_string .= " AND p.private = '1' ";
            }

            if (!empty($data['country']))
            {
                $query_string .= " AND p.country = :country ";
                $params['country'] = $data['country'];
            }

            if (!empty($data['residence']))
            {
                $query_string .= " AND p.residence LIKE CONCAT('%',:residence,'%') ";
                $params['residence'] = $data['residence'];
            }

            if (!empty($data['gender']))
            {
                $query_string .= " AND p.gender = :gender ";
                $params['gender'] = $data['gender'];
            }

            if (!empty($data['type']))
            {
                switch ($data['type'])
                {
                    case 'all':
                        $query_string .= " AND (p.type = 'vip' OR p.type = 'nip') ";
                        break;

                    case 'vip':
                        $query_string .= " AND p.type = 'vip' ";
                        break;

                    case 'animal':
                        $query_string .= " AND p.type = 'animal' ";
                        break;
                }
            }
			else
			{
				$query_string .= " AND (p.type = 'vip' OR p.type = 'nip') ";
			}

            $query_string .= " AND p.dateofdeath <= CURRENT_DATE() ";

            $query_string .= " AND (p.status = 'published' OR p.user = :userid) ";
            $params['userid'] = $this->getUser() ? $this->getUser()->getId() : 0;

            $query_string .= " ORDER BY p.dateofdeath DESC";

            $query = $this->getEm()->createQuery($query_string)->setParameters($params);

            $pages = $query->getResult();

            $abbrpagearr = $this->getAbbrPages($pages);

            return new \Zend\View\Model\JsonModel($abbrpagearr);
        }

        echo "error";
        die;
    }

    /**
     * Display search page
     *
     */
    public function getAction()
    {
        $searchterm = $this->params('searchterm') ? $this->params('searchterm') : null;
        $searchtype = $this->params('searchtype') ? $this->params('searchtype') : null;
        
        $view = $this->getView();

        // @TODO: do we have to check for PagePrivacySettings? -- everything should be findable, so we dont' need it in the query.
        // @TODO: let me know if there is a better search method -- Perhaps in the future thorw it in elasticsearch, to get better search options/results.
        // only search in dead people
        $query_string = "SELECT p FROM Application\Entity\Page p WHERE p.dateofdeath IS NOT NULL";
        $params = array();
        if ($searchterm)
        {
            /* searchterm can be:
             * page:        firstname.lastname
             * single word: firstname or lastname
             * double word: firstname and lastname
             */
            if (strpos($searchterm, '.') > 0)
            {
                // find page on url
                $query_string .= " AND p.url LIKE CONCAT('%',:searchterm,'%')";
                $params['searchterm'] = $searchterm;
            }
            elseif (strpos($searchterm, ' ') < 1)
            {
                // find single firstname OR lastname
                $query_string .= " AND ( p.firstname LIKE CONCAT('%',:searchterm,'%') OR p.lastname LIKE CONCAT('%',:searchterm,'%') )";
                $params['searchterm'] = $searchterm;
            }
            else
            {
                // find firstname AND lastname
                $names = explode(' ', $searchterm);
                $firstname = $names[0];
                $lastname = end($names);
                $query_string .= " AND p.firstname LIKE CONCAT('%',:firstname,'%') AND p.lastname LIKE CONCAT('%',:lastname,'%')";
                $params['firstname'] = $firstname;
                $params['lastname'] = $lastname;
            }
        }
        
        if ($searchtype && in_array($searchtype, array('animal', 'vip'))) {
            $query_string .= " AND p.type = '$searchtype'";
        } else {
            $query_string .= " AND (p.type = 'vip' OR p.type = 'nip') ";
        }

        $query_string .= " AND p.dateofdeath <= CURRENT_DATE() ";
        $query_string .= " AND (p.status = 'published' OR p.user = :userid )";
        $params['userid'] = $this->getUser() ? $this->getUser()->getId() : 0;
        $query_string .= " ORDER BY p.dateofdeath DESC";

        $query = $this->getEm()->createQuery($query_string)->setParameters($params);

        $pages = $query->getResult();

        $abbrpagearr = $this->getAbbrPages($pages);

        $view->setVariable('pages', $abbrpagearr);
        return $view;
    }

    protected function getAbbrPages($pages)
    {
        $abbrpagearr = array();
        foreach ($pages as $page)
        {
            $dob = $page->getDateOfBirth() ? $page->getDateOfBirth()->format('Y-m-d') : null;
            $dod = $page->getDateOfDeath() ? $page->getDateOfDeath()->format('Y-m-d') : null;
            $photo = $page->getPhoto();
            
            if ($photo)
                $photo = array('url' =>  $photo->getLocation(), 'roi' => $photo->getROI());
            else
                $photo = array('url' =>  '/images/user-icon-large.png', 'roi' => array('x' => null, 'y' => null, 'width' => null, 'height' => null));
            
            $abbrpagearr[] = array(
                'url' => $page->getUrl(),
                'firstname' => $page->getFirstname(),
                'lastname' => $page->getLastname(),
                'photo' => $photo,
                'dateofbirth' => $dob,
                'dateofdeath' => $dod,
                'private' => $page->getPrivate(),
                'adminid' => $page->getUser()->getId(), // for requesting invite
                'introtext' => $page->getIntrotext()
            );
        }

        return $abbrpagearr;
    }

    /**
     * Get last added public profiles
     */
    public function recentAction()
    {
        $view = $this->getView();

        // get 5 last added profiles which are public
        $query_string = "SELECT p FROM Application\Entity\Page p";
        $query_string .= " WHERE p.dateofdeath IS NOT NULL";
        $query_string .= " AND p.dateofdeath <= CURRENT_DATE() ";
        $query_string .= " AND p.status = 'published' ";
        $query_string .= " AND p.type != 'animal' ";
        $query_string .= " ORDER BY p.creationdate DESC";

        $query = $this->getEm()->createQuery($query_string)->setMaxResults(5);

        $pages = $query->getResult();

        $abbrpagearr = $this->getAbbrPages($pages);

        $view->setVariable('pages', $abbrpagearr);

        return $view;
    }

    public function randomAction()
    {
        $view = $this->getView();

        // get last added profiles which are public
        $query_string = "SELECT p FROM Application\Entity\Page p WHERE p.dateofdeath IS NOT NULL";
        $query_string .= " AND p.dateofdeath <= CURRENT_DATE() ";
        $query_string .= " AND p.status = 'published' ";
        $query_string .= " AND p.type = 'vip' ";
        $query_string .= " ORDER BY p.dateofdeath DESC";

        // Doctrine does not support random, so get some latest pages, shuffle and slice.
        // @TODO: this can be done better somehow?
        // something like this: https://gist.github.com/Ocramius/919465
        $query = $this->getEm()->createQuery($query_string)->setMaxResults(30);
        $pages = $query->getResult();
        shuffle($pages);
        $pages = array_slice($pages, 0, 3);

        $abbrpagearr = $this->getAbbrPages($pages);

        $view->setVariable('pages', $abbrpagearr);

        return $view;
    }

    public function rotatorsAction()
    {
        $view = $this->getView();

        // get last added profiles which are public
        $query_string = "SELECT p FROM Application\Entity\Page p WHERE p.dateofdeath IS NOT NULL";
        $query_string .= " AND p.dateofdeath <= CURRENT_DATE() ";
        $query_string .= " AND p.status = 'published' ";
        $query_string .= " AND p.rotating = 1 ";
        $query_string .= " ORDER BY p.dateofdeath DESC";

        $query = $this->getEm()->createQuery($query_string);
        $pages = $query->getResult();

        $abbrpagearr = $this->getAbbrPages($pages);

        $view->setVariable('pages', $abbrpagearr);

        return $view;
    }

}
