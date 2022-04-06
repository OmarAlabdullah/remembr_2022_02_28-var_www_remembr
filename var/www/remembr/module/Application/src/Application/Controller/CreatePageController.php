<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class CreatePageController extends PageController
{

	public function checkAccess($action)
	{
		if (parent::checkAccess($action))
		{
			return true;
		}

		switch($action)
		{
			case 'create':
				if (!$this->getRequest()->isPost())
				{
					throw new \Exception('not a post request', 405);
				}
				// intentional fallthrough
			case 'index':
			case 'step1info':
			case 'step2photo':
			case 'step3url':
			case 'step4publish':
			case 'step5invite':
			case 'step5cinviteemail':
			case 'step6done':
			case 'checkurlavailable':
			case 'geturlsuggestions':
//				if (! $this->getUser() )
//				{
//					throw new \Exception('login required required', 403);
//				}
				return true;
		}
		
		return false;
	}


	public function indexAction()
	{
		$view = $this->getView()->setVariable('step',1);
//		$view->setVariables(array(
//			'loggedin' => $loggedin,
//		));
		return $view;
	}


	public function step1InfoAction()
	{
		return $this->getView()->setVariable('step',1);
	}
	public function step2PhotoAction()
	{
		return $this->getView()->setVariable('step',2);
	}
	public function step3UrlAction()
	{
		return $this->getView()->setVariable('step',3);
	}
	public function step4PublishAction()
	{
		return $this->getView()->setVariable('step',4);
	}
	public function step5InviteAction()
	{
		return $this->getView()->setVariable('step',5);
	}
	public function step5aInviteFacebookAction()
	{
		return $this->getView()->setVariable('step',5);
	}
	public function step5cInviteEmailsAction()
	{
		return $this->getView()->setVariable('step',5);
	}
	public function step6DoneAction()
	{
		return $this->getView()->setVariable('step',6);
	}

	public function checkUrlAvailableAction()
	{
		$url = $this->params('url');
		$page = strpos($url, '.') < 1 ? null : $this->getEm()->getRepository('Application\Entity\Page')->findOneBy(array('url' => $url));
		return $this->getView()->setVariable('available', $page == null);
	}

	public function getUrlSuggestionsAction()
	{
		$data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);

		$firstname	= iconv('UTF-8', 'US-ASCII//TRANSLIT', $data['firstname']);
		$firstname  = preg_replace('/\s+/', '.', strtolower(trim($firstname)));
		$lastname	= iconv('UTF-8', 'US-ASCII//TRANSLIT', $data['lastname']);
		$lastname  = preg_replace('/\s+/', '.', strtolower(trim($lastname)));
		$dateofdeath= new \DateTime($data['dateofdeath']);
		$dateofbirth= new \DateTime($data['dateofbirth']);

		$checkedsuggestions = array();

		if ($firstname && $lastname)
		{
			$suggestions = array(
				$firstname.'.'.$lastname,
                $firstname[0].'.'.$lastname,
                $firstname.'.'.$lastname[0],
				$firstname.'.'.$lastname.'.'.$dateofbirth->format('Y'),
				$firstname.'.'.$lastname.'.'.$dateofdeath->format('Y'),
				$firstname.'.'.$dateofbirth->format('Y'),
				$firstname.'.'.$dateofdeath->format('Y'),
				$lastname.'.'.$dateofbirth->format('Y'),
				$lastname.'.'.$dateofdeath->format('Y'),
				$firstname.'.'.$lastname.'.'.$dateofbirth->format('Y.m.d'),
				$firstname.'.'.$lastname.'.'.$dateofdeath->format('Y.m.d'),
				$firstname.'.'.$dateofbirth->format('Y.m.d'),
				$firstname.'.'.$dateofdeath->format('Y.m.d'),
				$lastname.'.'.$dateofbirth->format('Y.m-d'),
				$lastname.'.'.$dateofdeath->format('Y.m.d'),
			);

			$k = 0;
			$c = array();
			foreach($suggestions as $sug)
			{
				if (empty($c[$sug]))
				{
					$c[$sug] = 1;
				}
				else // skip duplicates
				{
					continue;
				}

				if ( $this->getEm()->getRepository('Application\Entity\Page')->findOneBy(array('url' => $sug)) == null)
				{
					$checkedsuggestions[] = array('url' => $sug);
					if (++$k == 4)
					{
						break;
					}
				}
			}
		}

		return $this->getView()->setVariable('suggestions', $checkedsuggestions);
	}
        
	public function createAction()
	{
		$data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
		if (empty($data['publishnow']))
		{
			$data['status'] = \Application\Entity\Page::UNPUBLISHED;
		}
		else
		{
			$data['publishdate'] = date('Y-m-d');
			$data['status'] = \Application\Entity\Page::PUBLISHED;
		}
        $newpage = new \Application\Entity\Page();
		$annotationbuilder = new \Zend\Form\Annotation\AnnotationBuilder();
		$form = $annotationbuilder->createForm($newpage);

		$form->bind($newpage);
		$form->setData($data);
        
        if ($form->isValid())
		{
			// @TODO check user can create page
            $user = $this->getUserLib()->getUser() ?: null ;
			$newpage->setUser($user);
            if ($data['photo'] === '') {
                $newpage->setPhoto(null);
            } else {
                $newimage = (new \Application\Entity\Image())->setLocation($data['photo'])->setROI($data['roi']);
                $this->getEm()->persist($newimage);
                $newpage->setPhoto($newimage);
            }
            
            $this->getEm()->persist($newpage);
			$this->getEm()->flush();
            //throw new \Exception("STOP HAMMER TIME!");
            // add admin rights for owner.

			//@TODO check this works
			$ar = \Application\Rights::$admin;
			$rights = new \Auth\Entity\UserRight($user->getId(), '/Application\Entity\Page:'.$newpage->getId(), $ar->getGroup(), $ar->getValue());
			$this->getEm()->persist($rights);
			$this->getEm()->flush();


			$return = $newpage->getArrayCopy();
			$return['created'] = true;
			$return['currentstep'] = 6;

			if (is_array($data['invites']))
			{
				foreach($data['invites'] as $type => $invites)
				{
					switch($type)
					{
						case 'facebook'	: $return['invites']['facebook']= $this->facebookInvites($invites, $newpage); break;
						case 'remembr'	: $return['invites']['remembr']	= $this->remembrInvites($invites, $newpage); break;
						case 'email'	: $return['invites']['email']	= $this->emailInvites($invites,$newpage ); break;
					}

				}
			}
			return $this->getView()->setVariables($return);
		}

		$data['created'] = false;
		$data['currentstep'] = 6;
		/**
		 * @TODO find out why and report back;
		 */
		return $this->getView()->setVariables($data);
	}

}
