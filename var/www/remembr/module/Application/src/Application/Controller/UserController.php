<?php

namespace Application\Controller;

use Base\Controller\BaseController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class UserController extends BaseController
{

    public function checkAccess($action)
    {
        if ($this->params('format') != 'json')
        {
            if ($action == 'login' || $action == 'confirmnewsletter')
            {
                return true;
            }

            throw new \Exception('method not supported', 405);
        }

        if ($action != 'get' && !$this->getUser())
        {
            throw new \Exception('please log in', 401);
        }

        return true;
    }

    public function facebookFriendsAction()
    {
        return new \Zend\View\Model\JsonModel($this->loginApi()->getFBfriends());
    }

    public function getAction()
    {
        $view = $this->getView();

        $loggedin = $this->getUser();

        if ($loggedin)
        {
            //$this->getMessagesAction();
            $this->getNotificationsAction();    // @TODO not here
            $this->getPagesAction();
            $this->getPageAccessAction();

            //	$view->setVariable('notifications', $this->getNotifications());

            if ($loggedin->getProfile()->getDateofbirth() == null)
            {
                $dateofbirth = '';
            }
            else
            {
                $dateofbirth = $loggedin->getProfile()->getDateofbirth()->format('Y-m-d');
            }
        }


//        $translator = $this->getServiceLocator()->get('translator');
//		$language = substr($translator->getLocale(), 0, 2);

        $profile = $loggedin ? $loggedin->getProfile() : null;
        
        $view->setVariables(array(
            'loggedin' => !empty($loggedin),
            'dateformat' => 'dd-mm-yy', // ! datepicker format
            'name' => $profile ? $profile->getName() : 'Anoniem',
            'profilephoto' => $loggedin ? $this->getProfilePhoto() : '',
            'gender' => $profile ? $profile->getGender() : '',
            'language' => $profile ? $profile->getLanguage() : '',
//            'currentlang' => $language,
            'dateofbirth' => $loggedin ? $dateofbirth : '',
            'country' => $profile ? $profile->getCountry() : '',
            'residence' => $profile ? $profile->getResidence() : '',
            'id' => $profile ? $profile->getId() : '',
            'logins' => $profile ? $loggedin->getLogins() : ''
        ));
        return $view;
    }

    public function loginAction()
    {
        $view = $this->getView();
        return $view;
    }

    protected function getProfilePhoto()
    {
        $user = $this->getUser();

        if ($profilephoto = $user->getProfile()->getPhotoid())
        {
            return $profilephoto;
        }
        else
        {
            return $this->loginApi()->getProfilePhoto();
        }
    }

    /**
     * @TODO, much of what is below is now in the notification-controller?
     */
    public function setReadNotificationsAction()
    {

    }

    public function getNotificationsAction()
    {
        $v = $this->forward()->dispatch('Application\\Controller\\Notifications', array(
            'action' => 'getNotifications',
            'format' => 'json'
        ));
        $view = $this->getView();
        $view->setVariable('notifications', $v->getVariable('notifications'));
        return $view;
    }

    public function setReadMessagesAction()
    {

    }

    public function setReadpagesAction()
    {

    }

    protected function getDate($date)
    {
        $parts = explode(' ', $date);
        return $parts[0];
    }

    /**
     *
     * @TODO choose: return all pages you have access to (friend/admin). Or all
     * 				 pages you're an admin for. Or all pages you created
     * 	Currently returns all pages you're an admin of.
     */
    public function getPagesAction()
    {
        $view = $this->getView();

        $ar = \Application\Rights::$admin;

        // I'd rather use a nativeQuery and Resultmapping, but doctrine is a whiny little bitch.
        $query = $this->getEm()->getConnection()->getWrappedConnection()
                ->prepare("SELECT Page.id FROM Page JOIN UserRight ON Page.deletedAt Is NULL AND UserRight.user_id = ? AND UserRight.rightGroup = ? AND UserRight.value & ? AND UserRight.path = CONCAT(?, Page.id)");
        $query->execute(array(
            $this->getUser()->getId(),
            $ar->getGroup(),
            $ar->getValue(),
            '/Application\\Entity\\Page:'
        ));
        $pageids = call_user_func_array('array_merge', $query->fetchAll(\PDO::FETCH_NUM)? : array(array()));

        $abbrpagearr = array();
        if ($pageids)
        {
            $pages = $this->getEm()->getRepository('Application\Entity\Page')->findById($pageids);
            foreach ($pages as $page)
            {
                $dob = $page->getDateOfBirth() ? $page->getDateOfBirth()->format('Y-m-d') : null; // $this->getDate($page['dateofbirth']);
                $dod = $page->getDateOfDeath() ? $page->getDateOfDeath()->format('Y-m-d') : null; //$this->getDate($page['dateofdeath']);
                
                
                $abbrpagearr[$page->getUrl() /* ['url'] */] = array(
                    'firstname' => $page->getFirstName(), // ['firstname'],
                    'lastname' => $page->getLastName(), // ['lastname'],
                    //				'url' => $page['url'],
                    'photo' => array('url' => $page->getPhotoLocation(), 'roi' => $page->getPhotoROI(), 'cropRectangle' => array('x' => null, 'y' => null, 'image' => array('width' => null, 'height'=> null), 'canvas' => array('width' => null, 'height'=> null))),
                    'newcoms' => $page->getMemories()->count(),
                    'dateofbirth' => $dob,
                    'dateofdeath' => $dod,
                    'status' => $page->getStatus(),
                    'private' => $page->getPrivate(),
                );
            }
        }

        $view->setVariable('pages', $abbrpagearr);

        return $view;
    }
    
    private function getDraftPage() {
        if ($user = $this->getUser())
        {
            $drafts = $this->getEm()->getRepository('Application\Entity\DraftPage')->findBy(array('user' => $this->getUser()));
            if ( count($drafts) >  1 )
                throw new \Exception("User " . $user->getId() . " has more than one draft stored (" . count($drafts) . ").");
            if ( count($drafts) == 1 )
                return $drafts[0];
        }
        return null;
    }
    
    public function getDraftPageAction() {
        $draft = $this->getDraftPage();
        if ($draft === null)
            return $this->getView()->setVariable('draft', null);
        return $this->getView()->setVariable('draft', $draft->getArrayCopy());
    }
    
    public function updateDraftPageAction()
    {
        $draft = $this->getDraftPage();
        if ($draft === null) {
            $draft = new \Application\Entity\DraftPage();
            $this->getEm()->persist($draft);
        }
        
        $data = Json::decode(
            $this->getRequest()->getContent(),
            Json::TYPE_ARRAY
        );
        
        $annotationbuilder = new \Zend\Form\Annotation\AnnotationBuilder();
        $form = $annotationbuilder->createForm($draft);

        $form->setValidationGroup('firstname', 'lastname', 'dateofbirth', 'dateofdeath', 'introtext', 'gender', 'country', 'residence');
        $form->setData($data);

        if ($form->isValid())
        {
            $draft->setFirstname($data['firstname'])
                 ->setLastname($data['lastname'])
                 ->setDateofbirth($data['dateofbirth'] == '' ? null : new \DateTime($data['dateofbirth']))
                 ->setDateofdeath($data['dateofdeath'] == '' ? null : new \DateTime($data['dateofdeath']))
                 ->setIntrotext($data['introtext'])
                 ->setCountry($data['country'])
                 ->setResidence($data['residence'])
                 ->setGender($data['gender'])
                 ->setUrl($data['url'] ? : '')
                 ->setUser($this->getUser());
            $this->getEm()->flush();
            $return = $draft->getArrayCopy();
            $return['updated'] = true;

            return $this->getView()->setVariables($return);
        }

        $data['updated'] = false;
        
        return $this->getView()->setVariables($data);
    }
    
    public function deleteDraftPageAction() {
        $draft = $this->getDraftPage();
        if ($draft !== null) {
            $this->getEm()->remove($draft);
            $this->getEm()->flush();
        }
        return $this->getView()->setVariables(array('success' => true));
    }
    
    public function storeDraftPageAction() {
        return $this->getView()->setVariable('draft', $this->getDraftPage());
    }

    protected function getPageAccessAction()
    {
        if (!$this->getUser())
        {
            return array();
        }

        $fr = \Application\Rights::$friend;
        $ar = \Application\Rights::$admin;

        // I'd rather use a nativeQuery and Resultmapping, but doctrine is a whiny little bitch.
        $query = $this->getEm()->getConnection()->getWrappedConnection()
                ->prepare("SELECT Page.url FROM Page JOIN UserRight ON Page.private = 1 AND UserRight.user_id = ? AND UserRight.rightGroup = ? AND UserRight.value & ? AND UserRight.path = CONCAT(?, Page.id)");
        $query->execute(array(
            $this->getUser()->getId(),
            $ar->getGroup(),
            $ar->getValue() | $fr->getValue(),
            '/Application\\Entity\\Page:'
        ));
        $pageids = call_user_func_array('array_merge', $query->fetchAll(\PDO::FETCH_NUM)? : array(array()));

        $view = $this->getView();
        $view->setVariable('pageaccess', $pageids);

        return $view;
    }

    // confirm newsletter signin
    public function confirmNewsletterAction()
    {
        $key = $this->params('confirmkey', 'missing');
        $config = $this->getServiceLocator()->get('Config');
		$newsletterconfig = $config['TH']['ZfUser']['newslettermsg'][$this->getLang()];

        if ($user = $this->getEm()->getRepository('\Application\Entity\Newsletter')->findOneBy(array('confirmkey' => $key, 'confirmed' => false)))
        {
            $user->setConfirmed(true);
            $this->getEm()->flush();
            //
            $this->flashMessenger()->addMessage($newsletterconfig['confirmed']);
        }
        elseif ($this->getEm()->getRepository('\Application\Entity\Newsletter')->findOneBy(array('confirmkey' => $key, 'confirmed' => true)))
        {
            $this->flashMessenger()->addMessage($newsletterconfig['already_confirmed']);
        }
        else
        {
            $this->flashMessenger()->addMessage($newsletterconfig['not_found']);
        }

        return $this->redirect()->toUrl('/');
    }
}
