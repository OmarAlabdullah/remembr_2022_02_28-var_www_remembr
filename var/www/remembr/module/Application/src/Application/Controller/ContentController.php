<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Base\Controller\BaseController;
use Zend\Json\Json;
use Zend\Validator\EmailAddress;

class ContentController extends BaseController
{
	public function checkAccess($action)
	{
		switch($action)
		{
			/* just templates */
			case 'memorysmall':
			case 'condolencesmall':
			case 'photosmall':
			case 'videosmall':
			case 'youtubevideo':
			case 'infomessages':
			case 'simplepaging':
			case 'subheader':
			case 'header':
			case 'footer':
			case 'anonymousdelete':
				return true;

			case 'get': // memory + page + public or friend
				$mem = $this->getEm()->find('\Application\Entity\Memory', $this->params('id',0));
				if (!$mem)
				{
					throw new \Exception('memory not found',404);
				}
				if (!($page = $mem->getPage())) // may have been soft-deleted
				{
					throw new \Exception('page not found',404);
				}
				if ($page->getPrivate() &&
					! \Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin)  &&
					! \Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$friend)
					)
				{
					throw new \Exception('page access restricted', 403);
				}

				return true;
			case 'save': // page + public or friend + post
				/* additional type check in function, anything other than condolence needs user */

				if (! $this->getRequest()->isPost())
				{
					throw new \Exception('not a post request', 405);
				}
				// intentional fallthrough
			case 'index': // page + public or friend
				if (!($page = $this->params('page')))
				{
					throw new \Exception('page not found',404);
				}
				if ($page->getPrivate() &&
					! \Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin)  &&
					! \Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$friend)
					)
				{
					throw new \Exception('page access restricted', 403);
				}
				return true;

			case 'deletememory': // page-admin or creator + post
				// checked in function.
				return true;
            case 'edit': // page + public or friend + post
				/* additional type check in function, anything other than condolence needs user */

				if (! $this->getRequest()->isPost())
				{
					throw new \Exception('not a post request', 405);
				}
				return true;

		};
		return false;
	}

    public function indexAction()
    {
        $view = $this->getView();
        $page = $this->params('page');

		$memarr = array();
		foreach ($page->getMemories() as $memory)
		{
			$temp = array();
			$temp = $memory->getArrayCopy(1);
			$memarr[]= $temp;
		}
        $view->setVariable('memories', $memarr);

        return $view;
    }

    public function memorySmallAction()     {        return $this->getView();    }
    public function condolenceSmallAction() {        return $this->getView();    }
    public function photoSmallAction()      {        return $this->getView();    }
    public function videoSmallAction()      {        return $this->getView();    }
    public function youtubeVideoAction()    {        return $this->getView();    }

	/**
	 * @TODO the following four functions have nothing to do with the Content class/subclasses managed by this controller.
	 */
    public function infomessagesAction()    {        return $this->getView();    }
    public function simplepagingAction()    {        return $this->getView();    }
    public function headerAction()          {        return $this->getView();    }
    public function subheaderAction()          {        return $this->getView();    }
    public function footerAction()          {        return $this->getView();    }

    private function sendModificationEmail($page, $newcontent, $email, $lang)
    {
        $config = $this->getServiceLocator()->get('Config');
        $site_settings = $config['site_settings'];
        $mailer = $this->getServiceLocator()->get('SxMail\Service\SxMail');
        $sxMail = $mailer->prepare();
        
        //$pageurl = $this->url()->fromRoute('remembr/page', array('page' => $page, 'lang' => $lang), array('force_canonical' => true), 0);
        
        $viewModel = new ViewModel(array('anonymousName' => $newcontent->getUserName(), 'condolence' => $newcontent, 'page' => $page, 'lang' => $lang));
        $viewModel->setTemplate('mailtemplates/anonymousmodification.twig');

        $translator = $this->getServiceLocator()->get('translator');
        $subject = sprintf($translator->translate('Your condolence message has been posted'));

        $message = $sxMail->compose($viewModel);
        $message->setFrom($site_settings['noreply'], $site_settings['sitename'])
                ->setReplyTo($site_settings['noreply'])
                ->setSubject($subject)
                ->setTo($email);
        
        //error_log($message->getBodyText());
        
        $sxMail->send($message);
    }
    
    public function saveAction()
    {
        $page = $this->params('page');

		$data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);

		if ($data['type'] != 'condolence' && !$this->getUser())
		{
			throw new \Exception('user not logged in', 403);
		}
        
		if ($data['type'] == 'condolence' && !$this->getUser())
		{
            if (!isset($data['anonymousEmail']) || !$data['anonymousEmail'])
                throw new \Exception('no email address specified', 403);
                    
            $emailValidator = new \Zend\Validator\EmailAddress();
            if (! $emailValidator->isValid($data['anonymousEmail']))
                throw new \Exception('no valid email address specified', 403);
		}
		
		/* if type is media, check uploaded file. */

		switch ($data['type'])
		{
			case 'condolence' : $newcontent = new \Application\Entity\Condolence();
				break;
			case 'memory' : $newcontent = new \Application\Entity\Memory();
				break;
			case 'photo' : $newcontent = new \Application\Entity\Photo();
				break;
			case 'video' : $newcontent = new \Application\Entity\Video();
				break;
			default: throw new \Exception('invalid type');
		}

		$annotationbuilder = new \Zend\Form\Annotation\AnnotationBuilder();
		$form = $annotationbuilder->createForm($newcontent);

		$form->bind($newcontent);
		$form->setData($data);

		if ($form->isValid())
		{
            $sendEditLink = false;
			$user = $this->getUserLib()->getUser() ? : null;
			if ($user)
			{
				$newcontent->setUser($user);
			}
			elseif (isset($data['username'])) // non-users can post condolence, but need to enter username.
			{
				$newcontent->setUserName($data['username']);
                $sendEditLink = isset($data['anonymousEmail']) && $data['anonymousEmail'] && $data['anonymousEmail'] != '';
			}
			$newcontent->setPage($page);
            
            if ($data['type'] == 'condolence' && !$this->getUser())
                $newcontent->setAnonymousEmail($data['anonymousEmail']);

			if (!empty($data['labels']))
			{
				foreach ($data['labels'] as $id => $used)
				{
					if ($used AND $label = $this->getEm()->getReference('\Application\Entity\Label', $id))
					{
						$newcontent->addLabel($label);
					}
				}
			}

			if ($data['type'] == 'photo')
			{
				$newcontent->setPhotoId($data['photoid']); /* @TODO check it's valid */
			}
			if ($data['type'] == 'video')
			{
				// get video id for embedded youtube for these url's: http://rubular.com/r/M9PJYcQxRW
				$matches = array();
				preg_match('/(?<=(?:v|i)=)[a-zA-Z0-9-]+(?=&)|(?<=(?:v|i)\/)[^&\n]+|(?<=embed\/)[^"&\n]+|(?<=(?:v|i)=)[^&\n]+|(?<=youtu.be\/)[^&\n]+/', $data['videoid'], $matches);
				$videoid = $matches[0];
				$newcontent->setVideoId($videoid);
			}
            if ($sendEditLink) {
                $newcontent->setModificationKey(\Base\Util\Generator::generateKey(40, true));
            }
            
			$this->getEm()->persist($newcontent);
			$this->getEm()->flush();
			$return = $newcontent->getArrayCopy(1);
			$return['created'] = true;

			// notify admins of new content
			$this->notifyAdmin($newcontent, $user, $page);
            
            if ($sendEditLink) {
                $this->sendModificationEmail($page, $newcontent, $data['anonymousEmail'], $data['lang']);
            }

			return $this->getView()->setVariables($return);
		}

		$data['errors'] = $form->getMessages();
        $data['created'] = false;
        /**
         * @TODO find out why and report back;
         */
        return $this->getView()->setVariables($data);
    }
    
    private function returnError($error, $check_field='created') {
        return $this->getView()->setVariables([
            'errors' => [$error],
            $check_field => false
        ]);
    }
    
    public function editAction()
    {
		$data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $memory = $this->getEm()->find('\Application\Entity\Memory', $data['id']);
        $data = [ 'text'=> $data['text'] ]; // make sure nothing more comes in.
        
        if ($memory instanceof \Application\Entity\Video) 
            $data['videoid'] = $memory->getVideoId();
        if ($memory instanceof \Application\Entity\Photo)
            $data['photoid'] = $memory->getPhotoId();
        
		if (!$this->getUser())
            return $this->returnError ('you are not logged in');
        if ($this->getUser()->getId() != $memory->getUser()->getId())
            return $this->returnError ('you do not own this memory');
		
		$annotationbuilder = new \Zend\Form\Annotation\AnnotationBuilder();
		$form = $annotationbuilder->createForm($memory);

		$form->bind($memory);
		$form->setData($data);
		if ($form->isValid())
		{
			$this->getEm()->persist($memory);
			$this->getEm()->flush();
			$return = $memory->getArrayCopy(1);
			$return['changed'] = true;

			/* @TODO: should we notify admins of edited content ? */
			//$this->notifyAdmin($memory, $this->getUser(), $page);

			return $this->getView()->setVariables($return);
		}

		$data['errors'] = $form->getMessages();
        $data['changed'] = false;
        return $this->getView()->setVariables($data);
    }

    protected function notifyAdmin($newcontent, $user, $page)
    {
        // no notifications if sender = page owner
        if ($page->getUser() === $user) return;

        $data = array();
        $data['page'] = $page;
        $data['receiver'] = $page->getUser();
        $data['sender'] = $user;
        $data['memory'] = $newcontent;

        $notification = new \Application\Entity\Notification($data);

        $this->getEm()->persist($notification);
        $this->getEm()->flush();
    }

    public function getAction()
    {
        $view = $this->getView();
        $id = $this->params('id');
        $memory = $this->getEm()->find('\Application\Entity\Memory', $id);

        $view->setVariables($memory->getArrayCopy());
        $view->setVariable('pageurl', $memory->getPage()->getUrl());
        return $view;
    }

	/**
	 * @TODO rewrite frontend/backend to use regular json style, for consistency's sake.
	 */
    public function deleteMemoryAction()
    {
        $req = $this->getRequest();
        if ($req->isPost())
        {
            $data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);

            $id = $data['id'];

            if ($memory = $this->getEm()->getRepository('\Application\Entity\Memory')->findOneBy(array('id' => $id)))
            {
                $page =  $memory->getPage();
                // check if memory belongs to this user or if user has admin rights
                if ($memory->getUser() == $this->getUser() || \Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin))
                {
                    $this->getEm()->remove($memory);

                    // update 'newnotification' so owner gets signal this is deleted except if the user is the owner
                    if ($notification = $this->getEm()->getRepository('\Application\Entity\Notification')->findOneBy(array('memory' => $memory)))
                    {
                        if ($page->getUser() !== $this->getUser())
                        {
                            $notification->setNewnotification(true);
                            $notification->setDeleted(false);
                        }
                    }

                    $this->getEm()->flush();
                    echo "ok";
                    die;
                }
            }
            echo "error";
            die;
        }
    }
}
