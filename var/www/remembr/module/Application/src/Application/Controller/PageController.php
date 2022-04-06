<?php 

namespace Application\Controller;

use Base\Controller\BaseController;
use Zend\View\Model\ViewModel;
use TH\ZfUser\Controller\AccountController;
use Zend\Json\Json;

class PageController extends BaseController
{

    public function checkAccess($action)
    {
        
        switch ($action)
        {
            case 'editanonymouserror':   // post
                if ($this->params('format') != 'tpl')
                    throw new \Exception('only requests tpl allowed', 405);
                return true;
            case 'saveanonymouscondolence':   // post
                if (!$this->getRequest()->isPost())
                    throw new \Exception('not a post request', 405);
                if ($this->params('format') != 'json')
                    throw new \Exception('only requests json allowed', 405);
                return true;
            case 'deleteanonymouscondolence':
            case 'getanonymouscondolence':
                return true;
            case 'updateinfo':   // post & page & admin
            case 'updateprivacy':// post & page & admin
            case 'updatelabels': // post & page & admin
            case 'updatepublish':// post & page & admin
            case 'invite':       // post & page & admin
            case 'confirmremoval':       // post & page & admin
                if (!$this->getRequest()->isPost())
                {
                    throw new \Exception('not a post request', 405);
                }
                $page = $this->params('page');
                if (!($page instanceof \Application\Entity\Page ))
                {
                    throw new \Exception('profile-page not found', 410);
                }
                if (!\Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin))
                {
                    throw new \Exception('admin rights required', 401);
                }
                return true;

            case 'fileupload':  // post, page, media-privilege
                if (!$this->getRequest()->isPost())
                {
                    throw new \Exception('not a post request', 405);
                }
                $page = $this->params('page');

                if (!$this->getUser())
                {
                    throw new \Exception('user not logged in', 401);
                }

                if ($page)
                {
                    if (!($page instanceof \Application\Entity\Page ))
                    {
                        throw new \Exception('profile-page not found', 410);
                    }

                    if ($page->getPrivate() &&
                            !\Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin) &&
                            !\Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$friend)
                    )
                    {
                        throw new \Exception('user not allowed to add media', 410);
                    }
                }
                return true;

            case 'index': // page unless template
            case 'remove':
                $URI = $this->getRequest()->getUri();
                if ($this->params('format') != 'json')
                {
                    return true;
                }
                if (($page = $this->params('page')) instanceof \Application\Entity\Page)
                {
                    if ($page->getStatus() == \Application\Entity\Page::UNPUBLISHED &&
                            !\Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin))
                    {
                        throw new \Application\RemembrException("profile-page not yet available", 410, array(
                    'suberror' => 'notyetavailable'
                        ));
                    }
                    if ($page->getDeletedAt() ||
                            $page->getStatus() != \Application\Entity\Page::PUBLISHED &&
                            !\Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin)
                            )
                    {
                        throw new \Application\RemembrException("profile-page no longer available", 410, array(
                    'suberror' => 'nolongeravailable'
                        ));
                    }

                    if ($page->getPrivate() &&
                        !\Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$friend) &&
                        !\Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin))
                    {
                        throw new \Application\RemembrException("profile-page is private", 401, array(
                            'suberror' => 'inviterequired',
                            'data' => array(
                                'url'	        => $page->getUrl(),
                                'firstname'   => $page->getFirstname(),
                                'lastname'    => $page->getLastname(),
                                'dateofbirth' => $page->getDateOfBirth() ? $page->getDateOfBirth()->format('Y-m-d') : null,
                                'dateofdeath' => $page->getDateOfDeath() ? $page->getDateOfDeath()->format('Y-m-d') : null,
                                'photo'       => $page->getPhoto() && $page->getPhoto()->getLocation() ? $page->getPhoto()->getLocation() : '/images/user-icon-large.png',
                                'roi'         => $page->getPhoto() && $page->getPhoto()->getLocation() ? $page->getPhoto()->getROI() : null,
                                'private'	  => $page->getPrivate(),
                            )
                        ));
                    }
                    
                    return true;
                }
                throw new \Exception('profile-page not found', 410);

            case 'requestaccess': // template or loggedin
                if ($this->params('format') != 'json')
                {
                    return true;
                }
                if (!$this->getUser())
                {
                    throw new \Exception('user not logged in', 401);
                }
                $page = $this->params('page');
                if (!($page instanceof \Application\Entity\Page ))
                {
                    throw new \Exception('profile-page not found', 410);
                }
                return true;
            case 'grantaccess': // post,json,user+admin,page
                if (!$this->getRequest()->isPost())
                {
                    throw new \Exception('not a post request', 405);
                }
                if ($this->params('format') != 'json')
                {
                    throw new \Exception('format not available', 400);
                }
                if (!$this->getUser())
                {
                    throw new \Exception('user not logged in', 401);
                }
                $page = $this->params('page');
                if (!($page instanceof \Application\Entity\Page ))
                {
                    throw new \Exception('profile-page not found', 410);
                }
                if (!\Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin))
                {
                    throw new \Exception("sending invites requires admin rights", 401);
                }
                return true;
            case 'receive': // none /* maybe move to different controller? */
            case 'show':    // none
            case 'notFound':
                return true;
                break;
        }

        return false;
    }
    
	public function indexAction()
	{
        $view = $this->getView();
        
		if ($this->params('format') != 'tpl')
		{
			$page = $this->params('page');
            
			$vars = $page->getArrayCopy(1);
            
            $vars['roi'] = $page->getPhotoROI();
                        
			$detectedLanguages = \TH\LanguageDetect::detect($vars["introtext"], array("nl_NL", "en_US"));
			
			$vars['languageDetection'] = $detectedLanguages;
			
			$vars["detectedLocale"] = $detectedLanguages["nl_NL"] > $detectedLanguages["en_US"] ? "nl_NL" : "en_US";
			$vars["detectedLanguage"] = substr($vars["detectedLocale"],0,2);
			
			$uri = $this->getRequest()->getUri();
			$canonicalLang = 
				preg_match('#^/nl/#', $uri->getPath()) ? 'nl' :
                preg_match('#^/nl-be/#', $uri->getPath()) ? 'nl-be' :
				preg_match('#^/en/#', $uri->getPath()) ? 'en' : $vars["detectedLanguage"];
			$vars["canonicalBaseUrl"] = $uri->getScheme() . '://' . $uri->getHost() . '/';
			$vars["canonicalUrl"] = $vars["canonicalBaseUrl"] . $canonicalLang . '/' . $vars["url"];
			
			
			$view->setVariables($vars);
		}
		
		if ($this->params('format') == 'html')
			$view->setVariable('memories', $page->getMemories());
		return $view;
	}

    public function notFoundAction()
    {
        $view = $this->getView();
        $view->setVariable('page', $this->params('page'));
        //var_dump($view); die;
        return $view;
    }

//	public function memoryAction()
//	{
//		$page = $this->params('page');
//		$memories = array();
//		if ($page)
//		{
//			$memories = $page->getMemories();
//		}
//
//		$view = $this->getView();
////		$view->setVariable('format', 'json');
//		$view->setVariable('page', $page);
//		$view->setVariable('memories', $memories);
//		return $view;
//	}
//	public function settingsAction()
//	{
//		$page = $this->params('page');
//		$view = $this->getView();
//		$view->setVariable('page', $page);
//		return $view;
//	}

    public function showAction()
    {
        $view = $this->getView();
		if ($this->params('format') == 'html')
		{
			$memid = $this->params('id');
			$mem = $this->getEm()->find('\Application\Entity\Memory', $memid);
			$view->setVariable('memory', $mem);
		}
        return $view;
    }

    public function updateInfoAction()
    {
        // @TODO check user priveleges (!)

        $req = $this->getRequest();
        $view = $this->getView();

        if (!$req->isPost())
        {
            $this->getResponse()->setStatusCode(405);
            return $view->setVariable('error', 'not a post request');
        }

        $page = $this->params('page');
        if (!$page)
        {
            $this->getResponse()->setStatusCode(410);
            return $view->setVariable('error', 'page not found');
        }

        $data = Json::decode($req->getContent(), Json::TYPE_ARRAY);

        $annotationbuilder = new \Zend\Form\Annotation\AnnotationBuilder();
        $form = $annotationbuilder->createForm($page);

        $form->setValidationGroup('firstname', 'lastname', 'dateofbirth', 'dateofdeath', 'introtext', 'photo', 'gender', 'country', 'residence');
        $form->setData($data);

        if ($form->isValid())
        {
            if ($data['photo'] === '' && $page->getPhoto() !== null ) {
                $this->getEm()->remove($page->getPhoto());
                $page->setPhoto(null);
            }
            if ($data['photo'] !== '') {
                if ($page->getPhoto() === null) {
                    $page->setPhoto(new \Application\Entity\Image());
                }
                $page->getPhoto()->setLocation($data['photo'])->setROI($data['roi']);
                $this->getEm()->persist($page->getPhoto());
            }
            
            $page->setFirstname($data['firstname'])
                    ->setLastname($data['lastname'])
                    ->setDateofbirth(new \DateTime($data['dateofbirth']))
                    ->setDateofdeath(new \DateTime($data['dateofdeath']))
                    ->setIntrotext($data['introtext'])
                    ->setCountry($data['country'])
                    ->setResidence($data['residence'])
                    ->setGender($data['gender']);

            $this->getEm()->flush();
            $return = $page->getArrayCopy();
            $return['updated'] = true;

            return $this->getView()->setVariables($return);
        }

        $data['updated'] = false;
        /**
         * @TODO find out why and report back;
         */
        return $this->getView()->setVariables($data);
    }

    public function updatePrivacyAction()
    {
        // @TODO check user priveleges (!)

        $req = $this->getRequest();
        $view = $this->getView();

        if (!$req->isPost())
        {
            $this->getResponse()->setStatusCode(405);
            return $view->setVariable('error', 'not a post request');
        }

        $page = $this->params('page');
        if (!$page)
        {
            $this->getResponse()->setStatusCode(410);
            return $view->setVariable('error', 'page not found');
        }

        $data = Json::decode($req->getContent(), Json::TYPE_ARRAY);

        $annotationbuilder = new \Zend\Form\Annotation\AnnotationBuilder();
        $form = $annotationbuilder->createForm($page);
        $form->setValidationGroup('private');
        $form->setData($data);
        if (@$form->isValid())
        {
            $page->setPrivate($data['private']);
            $this->getEm()->flush();
            $return = $page->getArrayCopy(1);
            $return['updated'] = true;

            return $this->getView()->setVariables($return);
        }

        $data['updated'] = false;
        /**
         * @TODO find out why and report back;
         */
        return $this->getView()->setVariables($data);
    }

    public function updateLabelsAction()
    {
        // @TODO check user priveleges (!)

        $req = $this->getRequest();
        $view = $this->getView();

        if (!$req->isPost())
        {
            $this->getResponse()->setStatusCode(405);
            return $view->setVariable('error', 'not a post request');
        }

        $page = $this->params('page');
        if (!$page)
        {
            $this->getResponse()->setStatusCode(410);
            return $view->setVariable('error', 'page not found');
        }

        $data = Json::decode($req->getContent(), Json::TYPE_ARRAY);

        $page->setUseLabels(isset($data['uselabels']) && $data['uselabels']);

        $pagelabel = $page->getLabels();
        if (!empty($data['labels']) && is_array($data['labels']))
        {
            $postedlabels = $data['labels'];
            foreach ($postedlabels as $label)
            {
                if (!empty($label['new']) && !empty($label['name']))
                {
                    $nl = new \Application\Entity\Label();
                    $nl->setName($label['name']);
                    $this->getEm()->persist($nl);
                    $pagelabel->add($nl);
                }
                else if (!empty($label['delete']) && !empty($label['id']))
                {
                    $dl = $this->getEm()->getReference('\Application\Entity\Label', $label['id']);
                    $pagelabel->removeElement($dl);
                    $this->getEm()->remove($dl);
                    // @TODO decide whether to use label-ids or textlabels.
                    // using ids, we could have translatable labels and provide them systemwide.
                }
            }
        }

        $this->getEm()->flush();
        $return = $page->getArrayCopy(1);

        return $this->getView()->setVariables($return);
    }

    public function updatePublishAction()
    {
        // @TODO check user priveleges (!)

        $req = $this->getRequest();
        $view = $this->getView();

        if (!$req->isPost())
        {
            $this->getResponse()->setStatusCode(405);
            return $view->setVariable('error', 'not a post request');
        }

        $page = $this->params('page');
        if (!$page)
        {
            $this->getResponse()->setStatusCode(410);
            return $view->setVariable('error', 'page not found');
        }

        $data = Json::decode($req->getContent(), Json::TYPE_ARRAY);

        $curstatus = $page->getStatus();
        if (!empty($data['status']))
        {
            switch ($data['status'])
            {
                case $page::DEACTIVATED :
                    $page->setStatus($page::DEACTIVATED);
                    $page->setPublishdate(null);
                    break;
                case $page::TOBEDELETED :
                    $page->setStatus($page::TOBEDELETED);
                    $page->setPublishdate(null);
                    $this->sendRemovalConfirmation($page);
                    break;
                case $page::PUBLISHED :
                    $page->setStatus($page::PUBLISHED);
                    $page->setPublishdate(new \DateTime());
                    break;
                default:
                    $this->getResponse()->setStatusCode(400);
                    return $view->setVariable('error', 'invalid status');
            }
            $this->getEm()->flush();

            $return = $page->getArrayCopy();
            $return['updated'] = true;

            return $this->getView()->setVariables($return);
        }

        /*
          $annotationbuilder = new \Zend\Form\Annotation\AnnotationBuilder();
          $form = $annotationbuilder->createForm('\Application\Entity\Page');
          $form->setData($data);
          $form->setValidationGroup('publishdate');
          if ($form->isValid())
          {
          $page->setPublishdate(new \DateTime($data['publishdate']));
          $this->getEm()->flush();
          $return = $page->getArrayCopy();
          $return['updated'] = true;

          return $this->getView()->setVariables($return);
          }
         */

        $data['updated'] = false;
        /**
         * @TODO find out why and report back;
         */
        return $this->getView()->setVariables($data);
    }

    protected function sendRemovalConfirmation($page)
    {
        $confirm = new \Application\Entity\ConfirmAction('deletepage', array('pageurl' => $page->getUrl(), 'user' => $this->getUser()->getId()), 7);
        $this->getEm()->persist($confirm);

        $mailer = $this->getServiceLocator()->get('SxMail\Service\SxMail');

        $lang = $this->params('lang');
        $key = $confirm->getKey();
        $user = $this->getUser();
        $useremail = $user->getEmail();
        $username = $user->getProfile()->getFirstName() . ' ' . $user->getProfile()->getLastName();
        $profile = $page->getFirstName() . ' ' . $page->getLastName();

        $translator = $this->getServiceLocator()->get('translator');
        $subject = sprintf($translator->translate('Confirm removal memorial page'));

        $config = $this->getServiceLocator()->get('Config');
        $site_settings = $config['site_settings'];
        $pageurl = $this->url()->fromRoute('remembr/page', array('page' => $page, 'lang' => $lang), array('force_canonical' => true), 0);

        $removeurl = $this->url()->fromRoute(
					'remembr/page/action/wildcard',
					array('action' => 'remove', 'page' => $page, 'confirm' => $key, 'lang' => $lang),
					array('force_canonical'=>true),
					0);

        $sxMail = $mailer->prepare();
        $viewModel = new ViewModel(array(
            'profile' => trim($profile),
            'pageurl' => $pageurl,
            'removeurl' => $removeurl,
            'pagephoto' => $page->getPhoto() ? $page->getPhoto()->getLocation() : '',
            'username' => trim($username)
        ));

        $viewModel->setTemplate('mailtemplates/confirmpageremoval.twig');
        
        $message = $sxMail->compose($viewModel);
        $message->setFrom($site_settings['noreply'], $site_settings['sitename'])
                ->setReplyTo($site_settings['email_to'], $site_settings['sitename'])
                ->setSubject($subject)
                ->setTo($useremail);
        if ($sxMail->send($message))
        {
            $this->getEm()->flush();
            return true;
        }

        return false;
    }

    public function removeAction()
    {
        $page = $this->params('page');
        if ($page)
        {
            $confirmkey = $this->params('confirm', '');
            $confirmaction = $this->getEm()->getRepository('Application\Entity\ConfirmAction')
                    ->findOneBy(array('key' => $confirmkey, 'action' => 'deletepage'));
            if (!$confirmaction)
            {
                return $this->getView()->setVariable('error', 'invalid confirm key');
            }

            $cad = $confirmaction->getData();
            if ($page->getUrl() != $cad['pageurl'])
            {
                $this->redirect()->toRoute('remembr/page', array('page' => $page), array(), 0);
                return;
            }
        }

        return $this->getView();
    }

    public function confirmRemovalAction()
    {
        $req = $this->getRequest();
        $view = $this->getView();
        $page = $this->params('page');

        if ($page->getStatus() != \Application\Entity\Page::TOBEDELETED)
        {
            $this->getResponse()->setStatusCode(400);
            return $view->setVariable('error', 'page is not marked for deletion, so confirming removal does not make sense');
        }

        $data = Json::decode($req->getContent(), Json::TYPE_ARRAY);
        $confirmkey = isset($data['confirmkey']) ? $data['confirmkey'] : null;

        if (!$confirmkey)
        {
            $this->getResponse()->setStatusCode(401);
            return $view->setVariable('error', 'missing confirmation key');
        }

        $confirmaction = $this->getEm()->getRepository('Application\Entity\ConfirmAction')
                ->findOneBy(array('key' => $confirmkey, 'action' => 'deletepage'));

        if (!$confirmaction || $confirmaction->getExpirationDate() < new \DateTime())
        {
            $this->getResponse()->setStatusCode(401);
            return $view->setVariable('error', 'confirmation key not valid');
        }

        $cd = $confirmaction->getData();
        if ($page->getUrl() != $cd['pageurl'])
        {
            $this->getResponse()->setStatusCode(401);
            return $view->setVariable('error', 'confirmation key does not match');
        }

        $page->setStatus(\Application\Entity\Page::DELETED);
        $this->getEm()->flush(); // status won't be set if we don't flush before removal.
        $this->getEm()->remove($confirmaction);
        $this->getEm()->remove($page);
        $this->getEm()->flush();

        // @TODO notify users.


        $involvedUsers = array();
        foreach ($page->getMemories() as $mem)
        {
            $iuser = $mem->getUser();
            if ($iuser != $this->getUser())
            {
                $involvedUsers[$iuser->getId()] = $iuser;
            }
        }

        foreach ($involvedUsers as $iuser)
        {
            $this->getEm()->persist(new \Application\Entity\Notification(array(
                'page' => $page,
                'receiver' => $iuser,
                'sender' => $this->getUser(),
                'type' => 'deleted'
            )));
        }
        $this->getEm()->flush();

        $view->setVariable('removed', true);

        return $view;
    }

    public function receiveAction()
    {
        $invitekey = $this->params('invitee', 'missing');
        $invite = $this->getEm()->getRepository('Application\Entity\Invite')->findOneBy(array('key' => $invitekey));

        if (!$invite)
        {
            if ($page = $this->params('page'))
            {
                $this->redirect()->toRoute('remembr/page', array('page' => $page), array(), 0);
                return;
            }
            else
            {
                $this->redirect()->toRoute('remembr', array(), array(), 0);
                return;
            }
        }

        $page = $invite->getPage();
        if ($this->getUser())
        {
            $rights = $this->getRights();
            if (!\Auth\Rights\RightList::hasAny($rights, $page, \Application\Rights::$friend))
            {
                if (empty($rights['/Application\Entity\Page:' . $page->getId()]))
                {
                    $rights['/Application\Entity\Page:' . $page->getId()] = new \Auth\Rights\RightList();
                }
                $rights['/Application\Entity\Page:' . $page->getId()]->add(\Application\Rights::$friend);
                $this->syncRights($rights);
                $this->getEm()->flush();
            }
        }
        else
        {
            // @TODO remember accessrights for user, so they can be assigned at registration/login and allows use rto view page.
            $sc = new \Zend\Session\Container('pending_actions');
            if (empty($sc->invites))
            {
                $sc->invites = array();
            }

            // $sc->invites[] = $invitekey; // Does not work on live server for some reason!
			$sc->invites = array_merge($sc->invites, array($invitekey));
        }

        $this->redirect()->toRoute('remembr/page', array('page' => $page), array(), 0);
    }

    public function inviteAction()
    {
        // @TODO check user priveleges (!)

        $req = $this->getRequest();
        $view = $this->getView();

        if (!$req->isPost())
        {
            $this->getResponse()->setStatusCode(405);
            return $view->setVariable('error', 'not a post request');
        }

        $page = $this->params('page');
        if (!$page)
        {
            $this->getResponse()->setStatusCode(404);
            return $view->setVariable('error', 'page not found');
        }

        $data = Json::decode($req->getContent(), Json::TYPE_ARRAY);

        $return = array();
        foreach ($data as $type => $invite)
        {
            switch ($type)
            {
				case 'facebook'	: $return['facebook']	= $this->facebookInvites($invite, $page); break;
				case 'remembr'	: $return['remembr']	= $this->remembrInvites($invite, $page); break;
				case 'email'	: $return['email']		= $this->emailInvites($invite,$page ); break;
            }
        }

        return $this->getView()->setVariables($return);
    }

    /**
     * @TODO invites
     * @param type $invites
     * @return boolean
     */
    protected function facebookInvites($invite, $page)
    {
        return true;
    }

    protected function remembrInvites($invite, $page)
    {
        return true;
    }

    protected function emailInvites($invite, $page)
    {
        // @TODO validation
        if (empty($invite['recipients']) || trim($invite['recipients']) == '')
            return true;

        $recipients = array_map('trim', explode(';', $invite['recipients']));
        $subject = $invite['subject'];
        $text = $invite['text'];
        $mailer = $this->getServiceLocator()->get('SxMail\Service\SxMail');

        $user = $this->getUser();
        $useremail = $user->getEmail();
        $username = $user->getProfile()->getFirstName() . ' ' . $user->getProfile()->getLastName();

        $profile = $page->getFirstName() . ' ' . $page->getLastName();

        $config = $this->getServiceLocator()->get('Config');
        $site_settings = $config['site_settings'];
        $url = $this->url()->fromRoute('remembr/page', array('page' => $page), array('force_canonical' => true), 0);

        $sxMail = $mailer->prepare();
        $viewModel = new ViewModel(array(
            'text' => $text,
            'sender' => $username,
            'profile' => $profile,
            'shorturl' => $url,
            'profilephoto' => $this->loginApi()->getProfilePhoto(),
            'pagephoto' => $page->getPhoto()
        ));

        $viewModel->setTemplate('mailtemplates/invite.twig');

        foreach ($recipients as $email)
        {
            // this is for linking friend status to user when (s)he clicks the invite.
            $invite = new \Application\Entity\Invite();
            $invite->setPage($page)->setEmail($email);
            $this->getEm()->persist($invite);

            $key = $invite->getKey();
            $url = $this->url()->fromRoute(
					'remembr/page/action/wildcard',
					array('action' => 'receive', 'page' => $page, 'invitee' => $key),
					array('force_canonical'=>true),
					0);

            $viewModel->setVariable('url', $url); //@TODO

            $message = $sxMail->compose($viewModel);
            $message->setFrom($site_settings['noreply'], $site_settings['sitename'])
                    ->setReplyTo($useremail, $username)
                    ->setSubject($subject)
                    ->setTo($email);
            $sxMail->send($message);
        }
        $this->getEm()->flush();

        return true;
    }

    public function fileUploadAction()
    {
        $req = $this->getRequest();
        $view = $this->getView();

        if (!$req->isPost())
        {
            $this->getResponse()->setStatusCode(405);
            return $view->setVariable('error', 'not a post request');
        }

//		$page = $this->params('page');
//		if (! $page) {
//			$this->getResponse()->setStatusCode(404);
//			return $view->setVariable('error', 'page not found');
//		}

        $files = $req->getFiles();
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $ext = '';
        switch ($finfo->file($files['file']['tmp_name']))
        {
            case 'image/gif': $ext = '.gif';
                break;
            case 'image/png': $ext = '.png';
                break;
            case 'image/jpeg': $ext = '.jpg';
                break;
        }
        $url = \Base\Util\Generator::generateKey(16);
        // $page->getUrl();
        $filter = new \Zend\Filter\File\RenameUpload(array(
            'target' => "./data/uploads/$url$ext",
            'overwrite' => true
        ));
        $res = $filter->filter($files['file']);

        if ($res)
        {
            // for IE, else it asks to download/save the data...
            // @TODO add to the appropriate controller
            $this->getServiceLocator()->get('Application')->getEventManager()->attach(\Zend\Mvc\MvcEvent::EVENT_RENDER, function($event) {
                        $event->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'text/html');
                    }, -10000);

            $scale_factor = $this->processImage($url . $ext);

            $view->setVariable('scale_factor', $scale_factor);
            
            return $view->setVariable('photo', "/uploads/$url$ext");
        }
        else
        {
            $this->getResponse()->setStatusCode(500);
            return $view->setVariable('error', '');
        }
    }

    /**
     * Resize image to max height/width and crop to max dimensions
     *
     * @param string $imgurl
     */
    protected function processImage($imgurl)
    {
        $config = $this->getServiceLocator()->get('Config');
        $img_dimensions = $config['image_upload_settings'];

        $imagine = new \Imagine\Gd\Imagine();
        $image = $imagine->open("./data/uploads/$imgurl");

        $width = $image->getSize()->getWidth();
        $height = $image->getSize()->getHeight();

        //if ($width > $height)
				if ($width < $height)
        {
            $image->resize(
                    $image->getSize()->heighten($img_dimensions['height'])
            );
            
            $scale_factor = $img_dimensions['height'] / $height;
            
            // update dimension
            $width = $image->getSize()->getWidth();
            //we center the crop in relation to the width
            //$cropPoint = new \Imagine\Image\Point((max($width - $img_dimensions['width'], 0)) / 2, 0);
        }
        else
        {
            $image->resize(
                    $image->getSize()->widen($img_dimensions['width'])
            );
            
            $scale_factor = $img_dimensions['width'] / $width;
            
            // update dimension
            $height = $image->getSize()->getHeight();
            //we center the crop in relation to the height
            //$cropPoint = new \Imagine\Image\Point(0, (max($height - $img_dimensions['height'], 0)) / 2);
        }

        //$image->crop($cropPoint, new \Imagine\Image\Box($img_dimensions['width'], $img_dimensions['height']));
        $image->save("./data/uploads/$imgurl");
        return $scale_factor;
    }

    public function requestaccessAction()
    {
        $view = $this->getView();
        if ($this->params('format') == 'json')
        {
            $page = $this->params('page');
            $translator = $this->getServiceLocator()->get('translator');

            $profile = $this->getUser()->getProfile();
            $username = $profile->getFirstName() . ' ' . $profile->getLastName();

            $req = $this->getRequest();
            $req->setMethod('POST');
            $req->setContent(json_encode(
                            array(
                                'id' => $page->getUser()->getProfile()->getId(), // @TODO instead for user, use admin rights on page to find user; maybe send to all admins?
                                'title' => $translator->translate('Request for invite'),
                                'content' => sprintf($translator->translate('%s would like an invitation for %s'), $username, $page->getURL()),
                                'extra' => array(
                                    'type' => 'request',
                                    'pageid' => $page->getId(),
                                    'pageurl' => $page->getURL()
                                )
                    ))
            );

            $v = $this->forward()->dispatch('Application\\Controller\\Messages', array(
                'action' => 'new',
                'format' => 'json'
            ));

            $view = $this->getView();
            $view->setVariable('success', true); //? Maybe return message so it can be added to outbox.
        }

        return $view;
    }

    public function grantaccessAction()
    {
        $view = $this->getView();
        $page = $this->params('page');
        $data = json_decode($this->getRequest()->getContent(), TRUE) ? : $this->getRequest()->getPost();

        $msgid = $data['msgid'];

        $translator = $this->getServiceLocator()->get('translator');

        // get the message containing the invite request, check that it is a request and not already granted.
        $msg = $this->getEm()->find('Application\Entity\MessageCentreMessage', $msgid);

        $extra = $msg->getExtra();

        if (empty($extra['type']) || $extra['type'] != 'request')
        {
            throw new \Exception('Message is not a request that can be granted', 400);
        }
        if (isset($extra['granted']))
        {
            throw new \Exception('Request already granted', 400);
        }

        // mark invite request as granted.
        $extra['granted'] = true;
        $msg->setExtra($extra);

        // get the user that originally made a request for invite.
        $orig = $this->getEm()->getRepository('Application\Entity\MessageCentreOutbox')->findOneBy(array('message' => $msgid));
        $requester = $orig->getFrom();

        // set rights
        $rights = $this->getRights($requester);
        if (!\Auth\Rights\RightList::hasAny($rights, $page, \Application\Rights::$friend))
        {
            if (empty($rights['/Application\Entity\Page:' . $page->getId()]))
            {
                $rights['/Application\Entity\Page:' . $page->getId()] = new \Auth\Rights\RightList();
            }
            $rights['/Application\Entity\Page:' . $page->getId()]->add(\Application\Rights::$friend);
            $this->syncRights($rights, $requester);
        }
        $this->getEm()->flush();

        // send message
        $req = $this->getRequest();
        $req->setMethod('POST');
        $req->setContent(json_encode(
                        array(
                            'id' => $requester->getProfile()->getId(), // @TODO account-id would be nicer, but then everything should return that with the user.
                            'title' => $translator->translate('Invitation'),
                            'content' => sprintf($translator->translate('You\'ve been invited to %s'), $page->getURL()),
                            'extra' => array(
                                'type' => 'link',
                                'url' => $page->getURL()
                            )
                ))
        );

        $v = $this->forward()->dispatch('Application\\Controller\\Messages', array(
            'action' => 'new',
            'format' => 'json'
        ));

        $view = $this->getView();
        $view->setVariable('success', true); //? Maybe return message so it can be added to outbox.

        return $view;
    }
    
    private function getEditMemory($id = null)
    {
        $key = $this->params('key');
        $page = $this->params('page');
        
        if (!is_string($key) || strlen($key) != 40)
            return null;
        
        $checks = array('modificationKey' => $key, 'page' => $page, 'deletedAt' => null);
        if (! is_null($id) )
            $checks['id'] = $id;
        
        if ($memory = $this->getEm()->getRepository('\Application\Entity\Memory')->findOneBy($checks))
        {
            if (! $memory instanceof \Application\Entity\Condolence)
                return null;
            return $memory;
        }
        return null;
    }
    
    public function getAnonymousCondolenceAction() {
        $view = $this->getView();
        $page = $this->params('page');
        if (($memory = $this->getEditMemory()) !== null)
            $view->setVariables(array_merge ($memory->getArrayCopy(), array('key' => $this->params('key'))));
        else
            $this->getResponse()->setStatusCode(404);
        return $view;
    }
    
    public function saveAnonymousCondolenceAction() {
        $data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        
        if ($data['id'] == null) {
            echo "{\"status\": \"no-id\"}"; die;
        }
        
        if ($memory = $this->getEditMemory($data['id'])) {
            if (!is_string($data['text']) || strlen($data['text']) == 0) {
                echo "{\"status\": \"no-text\"}"; die;
            }
            
            $memory->setText($data['text']);

            // update 'newnotification' so owner gets signal this is deleted except if the user is the owner
            if ($this->params('page')->getUser() !== $this->getUser() && 
                ($notification = $this->getEm()->getRepository('\Application\Entity\Notification')->findOneBy(array('memory' => $memory)))
            )
            {
                $notification->setNewnotification(true);
                $notification->setDeleted(false);
            }

            $this->getEm()->flush();
            echo "{\"status\": \"ok\"}";
            die;
        }
        echo "{\"status\": \"not-found\"}";
        die;
    }
    
    public function deleteAnonymousCondolenceAction() {
        if ($memory = $this->getEditMemory()) {
            $this->getEm()->remove($memory);

            if ($this->params('page')->getUser() !== $this->getUser() && 
                ($notification = $this->getEm()->getRepository('\Application\Entity\Notification')->findOneBy(array('memory' => $memory)))
            )
            {
                $notification->setNewnotification(true);
                $notification->setDeleted(false);
            }

            $this->getEm()->flush();
            echo "{\"status\": \"ok\"}";
            die;
        }
        echo "{\"status\": \"not-found\"}";
        die;
    }
}
