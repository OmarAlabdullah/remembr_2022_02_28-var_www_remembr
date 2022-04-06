<?php

namespace Base\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mvc\MvcEvent;

abstract class BaseController extends AbstractActionController
{
	protected static $VALID_FORM = 0;
	protected static $INVALID_FORM = 1;
	protected static $NOT_POST = -1;
					
	protected $entityManager;
	protected $elasticsearch;
	protected $view;
	protected $log;
	protected $service = null;
	protected $user = null;

	protected $rights;
	protected $userlib = null;

	protected function getUserLib()
	{
		if (!$this->userlib)
		{
			$this->userlib = $this->getServiceLocator()->get('TH\ZfUser\Library\UserLib');
		}
		
		return $this->userlib;
	}

		protected function getGoogleApiId()
		{
			$config = $this->getServiceManager()->get('Config');
			return isset($config['google']['analytics']['id']) ? $config['google']['analytics']['id'] : '';
		}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function getEm()
	{
		if (!$this->entityManager)
		{
			$this->entityManager = $this->getServiceLocator()->get('th_entitymanager');
		}
		return $this->entityManager;
	}

	/**
	 * @return \Base\Util\ElasticSearch
	 */
	protected function getEs()
	{
		if (!$this->elasticsearch)
		{
			$config = $this->getServiceLocator()->get('config');
			$this->elasticsearch = new \Base\Util\ElasticSearch($config['elasticsearch']['host'], $config['elasticsearch']['port'], $config['elasticsearch']['index']);
		}
		return $this->elasticsearch;
	}

	/**
	 * @return Zend\Log
	 */
	protected function getLog() {
		if (!$this->log) {
			$this->log = $this->getServiceLocator()->get('Zend\Log');
		}
		return $this->log;
	}

	protected function getView()
	{
		if (!$this->view)
		{
			if ($this->params('format') == 'json' || $this->params()->fromQuery('json') !== null)
			{
				$this->view = new JsonModel();
			}
			else
			{
				$this->view = new ViewModel();
				if ($this->params('format') == 'tpl')
				{
					$this->view->setTerminal(true)->setVariable('format', 'tpl');
				}
			}
		}
		return $this->view;
	}

	protected function getIdentity() {
		if (!$this->service)
			$this->service = new \Zend\Authentication\AuthenticationService();
		return $this->service->getIdentity();
	}

	protected function getUser() {
		if ($this->user === null) {
			$this->user = $this->getUserLib()->getUser();
		}
		return $this->user;
	}

	protected function getRights($user = null)
	{
		$user = $user ?: $this->getUser();
		if (!$user)
		{
			return array();
		}
		$uid = $user->getId();
		if (empty($this->rights[$uid]))
		{
			$this->rights[$uid] = \Auth\Rights\RightList::fromDoctrine($this->getEm()->getRepository('Auth\Entity\UserRight')->findBy(array('user' => $user->getId())));
		}
		return $this->rights[$uid];
	}

	protected function syncRights($rightlist, $user = null)
	{
		$user = $user ?: $this->getUser();
		if (!$user)
		{
			return array();
		}
		$uid = $user->getId();

		$userrights = $this->getEm()->getRepository('Auth\Entity\UserRight')->findBy(array('user' => $uid));
		$userrights =  new \Doctrine\Common\Collections\ArrayCollection($userrights);
		$this->rights[$uid] = $rightlist;

		foreach ($rightlist as $p => $rl)
		{
			$rl->syncToDoctrine(
				$userrights,
				function($item) use ($p) { return $item->getPath() != $p; },
				function($name, $value) use ($uid, $p) { return new \Auth\Entity\UserRight($uid, $p, $name, $value); }
			);
		}
		foreach ($userrights as $ur)
		{
			$this->getEm()->persist($ur);
		}
	}


	/**
	 * This is a modification of AbstractActionController::onDispatch.
	 */
	public function onDispatch(MvcEvent $e) {
		$routeMatch = $e->getRouteMatch();
		if (!$routeMatch) {
			/**
			 * @todo Determine requirements for when route match is missing.
			 *       Potentially allow pulling directly from request metadata?
			 */
			throw new Exception\DomainException('Missing route matches; unsure how to retrieve action');
		}

		/**
		 * If we're just getting the template, then we won't need to modify the session.
		 */
/* Seems to be causing problems since recently.
 * @TODO figure out why.
		$format = $routeMatch->getParam('format');
		if ($format == 'tpl')
		{
			$this->getServiceLocator()->get('Zend\Session\SessionManager')->writeClose();
		}
*/

		/* check if there is an invite giving the user extra rights, or in the future possibly other delayed actions */
		if ($this->getUser())
		{
			$sc = new \Zend\Session\Container('pending_actions');
			if (! empty($sc->invites))
			{
				$rights = $this->getRights();
				foreach($sc->invites as $invitekey)
				{
					$invite = $this->getEm()->getRepository('Application\Entity\Invite')->findOneBy(array('key' => $invitekey));

					if (!$invite)
					{
						continue;
					}

					$page = $invite->getPage();
					if (! \Auth\Rights\RightList::hasAny($rights, $page, \Application\Rights::$friend) )
					{
						if (empty($rights['/Application\Entity\Page:'.$page->getId()]))
						{
							$rights['/Application\Entity\Page:'.$page->getId()] =  new \Auth\Rights\RightList();
						}
						$rights['/Application\Entity\Page:'.$page->getId()]->add(\Application\Rights::$friend);
					}
				}
				unset($sc->invites);
				$this->syncRights($rights);
				$this->getEm()->flush();
			}
		}


		$action = $routeMatch->getParam('action', 'not-found');
		$method = static::getMethodFromAction($action);
		$action = strtolower(substr($method,0,-6));

		if (!method_exists($this, $method)) {
			$method = 'notFoundAction';
		} else {
			$access = true;
			if (method_exists($this, 'checkAccess')) {
				$access = $this->checkAccess($action);
			}
			if ($access && method_exists($this, $action.'Access')) {
				$access = $this->{$action.'Access'}();
			}
			if (!$access) {
				if ($result = $e->getResult())
				{
					return $result;
				}
				$method = 'forbiddenAction';
			}
		}


		$actionResponse = $this->$method();

		$e->setResult($actionResponse);

		return $actionResponse;
	}

	/**
	 * @TODO improve error feedback.
	 *
	 */
	protected function forbiddenAction($message = '')
	{
        $this->view = $this->getView();
        $this->view->message = $message;

		$this->layout('layout/error');
		return $this->view->setTemplate('error/index');
	}

	public function errorAction($message = '') {
		$this->view = $this->getView();

		$this->view->setTemplate('layout/error');
		$this->view->setVariable('errormsg', $message);
		$this->view->setTerminal(true);
		return $this->view;
	}

	protected function getLang()
	{
		$translator = $this->getServiceLocator()->get('translator');
		return substr($translator->getLocale(), 0, 2);
	}
	
	protected function rawStringResponse($val) {
		$this->response->setContent($val);
		return $this->response;
	}

	public function injectFormData($form) {
		$req = $this->getRequest();
		if ($req->isPost()) {
			$data = json_decode(
				file_get_contents("php://input"),
				TRUE
			) ? : $req->getPost();

			$form->setData($data);
			return $form->isValid() ? BaseController::$VALID_FORM : BaseController::$INVALID_FORM;
		} else return BaseController::$NOT_POST;
	}
	
}

?>
