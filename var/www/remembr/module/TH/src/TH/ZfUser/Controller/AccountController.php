<?php

/**
 * Methods for login, signup, forgot password, edit account.
 */

namespace TH\ZfUser\Controller;

use Base\Controller\BaseController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Session\Storage\ArrayStorage;
use Zend\Json\Json;

//use TH\ZfUser\Library\UserLib;

class AccountController extends BaseController
{

    protected $loginActionLoggedOut = true;
    protected $confirmActionLoggedOut = true;
    protected $forgotpasswordActionLoggedOut = true;
    protected $forgotpassword2ActionLoggedOut = true;
    protected $signupActionLoggedOut = true;
    protected $loginProvidersActionLoggedOut = true;
    protected $logoutProvidersActionLoggedOut = true;
    protected $confirmSignupActionLoggedOut = true;
    protected $editActionLoggedOut = true;
    protected $updateUserActionLoggedOut = true;
    protected $logoutActionLoggedOut = true;
    protected $reactivateAccountActionLoggedOut = true;
    protected $user;
    protected $userlib;
    protected $sessionManager;
    protected $config;

    protected function getSessionManager()
    {
        if (!$this->sessionManager)
        {
            $this->sessionManager = $this->serviceLocator->get('Zend\Session\SessionManager');
        }
        return $this->sessionManager;
    }

    protected function getConfig()
    {
        if (!$this->config)
        {
            $this->config = $this->serviceLocator->get('config');
        }
        return $this->config;
    }

    /**
     * Display login form and handle login.
     */
    public function loginAction()
    {
        $sm = $this->getSessionManager();
        $storage = $sm->getStorage();
        $config = $this->getConfig();

        // Remember me?
        if ($sm->sessionExists() && !empty($storage["Zend_Auth"]->storage['userId']))
        {
            $user_id = $storage["Zend_Auth"]->storage['userId'];
            $user_session = new Container('user');
            $user_session->user_id = $user_id;

            // Regenerate expire date (regenerateId is called too).
            $sm->rememberMe($config['TH']['ZfUser']['remember_time']);
        }

        $form = new \TH\ZfUser\Form\LoginForm(new \TH\ZfUser\Rights\AppAuthAdapter($this->getEm(), $this->getLog()), 'login');
				
        $req = $this->getRequest();
        if ($req->isPost())
        {
            $data = json_decode(file_get_contents("php://input"), TRUE) ? : $req->getPost();
            $form->setData($data);

            if ($form->isValid())
            {
                $data = $form->getData();

                $this->processDefaultLogin($data);
            }
            else
            {
                //$this->flashMessenger()->addMessage('Sorry, user not found or not verified.');
                //echo "Sorry, user not found or not verified.";
            }
        }

        $result = new ViewModel();
        $result->setTerminal(true);
        $result->setVariables(array(
            'form' => $form,
        ));
        return $result;
    }

    /**
     * Management for: edit profile, your logins and logout
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function managementAction()
    {
        $sm = $this->getSessionManager();
        $storage = $sm->getStorage();
        $config = $this->getConfig();

        // Remember me?
        if ($sm->sessionExists() && !empty($storage["Zend_Auth"]->storage['userId']))
        {
            $user_id = $storage["Zend_Auth"]->storage['userId'];
            $user_session = new Container('user');
            $user_session->user_id = $user_id;

            // Regenerate expire date (regenerateId is called too).
            $sm->rememberMe($config['TH']['ZfUser']['remember_time']);
        }

        $user = $this->getUserLib()->getUser();
        $username = $user->getProfile()->getName();

        $result = new ViewModel();
        $result->setTerminal(true);
        $result->setVariables(array(
            'username' => $username
        ));
        return $result;
    }

    public function logoutProvidersAction()
    {
        $providers = array();

        // Get all valid providers for this account (for logout functionality).
        $allproviders = $this->hybridauth()->getConnectedProviders();
        $allproviders['default'] = 'default';

        foreach ($allproviders as $provider)
        {
            $provider = strtolower(strip_tags($provider));
            if ($provider === 'default' || $this->hybridauth()->isConnectedWith($provider))
            {
                $providers[$provider] = $provider;
            }
        }

        $active_providers = $this->LoginApi()->getAvailableProviders();

        // check if all these providers are still set in config file.
        foreach ($providers as $idx => $provider)
        {
            if (!in_array($provider, $active_providers))
            {
                unset($providers[$idx]);
            }
        }
        $providers[] = 'default';

        $result = new ViewModel();
        $result->setTerminal(true);
        $result->setVariables(array(
            'providers' => $providers,
            'active_providers' => $active_providers,
        ));
        return $result;
    }

    protected function toHomePage()
    {
        $config = $this->getConfig();
        $site_settings = $config['site_settings'];

        return $this->redirect()->toRoute($site_settings['home']['route'], array(
                    'controller' => $site_settings['home']['controller'],
                    'action' => $site_settings['home']['action']
        ));
    }

    /**
     *  Set user session after a normal login and process this "default" login.
     *
     * @param array $data formdata
     */
    protected function processDefaultLogin($formdata)
    {
        $sm = $this->getSessionManager();
        $config = $this->getConfig();

        if ($user = $this->getEm()->getRepository('TH\ZfUser\Entity\UserAccount')->findOneBy(array('email' => $formdata['email'], 'verified' => '1', 'deleted' => '0')))    // deliberately
        {
            $user_session = new Container('user');
            $user_session->user_id = $user->getId();

            // We have a default user: restore hybridauth session from dB.
            // In SocialloginController/loginComplete() the providers in this session are checked for validity.
            $this->hybridauth()->restoreSessionData($user->getHybridauthSession());

            $provider_session = new Container('provider');
            $provider_session->provider = 'default';

            if ($formdata['rememberme'])
            {
                $sm->rememberMe($config['TH']['ZfUser']['remember_time']);
            }

            echo "done";
            die;
        }
        elseif ($this->getEm()->getRepository('TH\ZfUser\Entity\UserAccount')->findOneBy(array('email' => $formdata['email'], 'verified' => '0')))
        {
            $service = new \Zend\Authentication\AuthenticationService();
            $service->clearIdentity();

            $this->getSessionManager()->forgetMe();

            echo "not-verified";
            die;
        }
        else
        {
            $service = new \Zend\Authentication\AuthenticationService();
            $service->clearIdentity();

            $this->getSessionManager()->forgetMe();

            die;
        }
    }
		
		public function sendConfirmationMail($user) {
			$translator = $this->getServiceLocator()->get('translator');
			$subject = sprintf($translator->translate('Confirm your e-mail address'));
			$site_settings = $this->getConfig()['site_settings'];
        
			$sm = $this->getServiceLocator();
			$mail = $sm->get('SxMail\Service\SxMail');
			$sxMail = $mail->prepare();
			$viewModel = new ViewModel(array('user' => $user));
			$viewModel->setTemplate('th/account/confirmMail.twig');
			$message = $sxMail->compose($viewModel);
			$message->setFrom($site_settings['noreply'], $site_settings['sitename'])
							->setTo($user->getEmail())
							->setSubject($subject);
			$sxMail->send($message);
		}
		
	public function resendConfirmationMailAction()
	{
		$form = new \TH\ZfUser\Form\ReconfirmAccountForm('reconfirm');
		switch ($this->injectFormData($form)) {
			case BaseController::$INVALID_FORM:
				return $this->rawStringResponse("invalid");
			case BaseController::$NOT_POST:
				$result = new ViewModel();
				$result->setTerminal(true);
				$result->setVariables(array(
						'form' => $form
				));
				return $result;
			case BaseController::$VALID_FORM:
				$email = $form->getData()['email'];

				// Only unique e-mail addresses are allowed.
				$user = $this->getEm()->getRepository('\TH\ZfUser\Entity\UserAccount')->findOneBy(array('email' => $email, 'deleted'=>false));
				if (!$user)
					return $this->rawStringResponse("user-not-found");

				if ($user->getKey() == null)
					return $this->rawStringResponse("already-confirmed");

				$user->setConfirmRequest(new \DateTime(date('Y-m-d H:i:s')));
				$user->setKey(\Base\Util\Generator::generateKey(40));
				$this->getEm()->flush();

				$this->sendConfirmationMail($user);
				return $this->rawStringResponse("done");
		}
	}

	public function signupAction()
	{
		$form = new \TH\ZfUser\Form\CreateAccountForm('signup');
		switch ($this->injectFormData($form)) {
			case BaseController::$INVALID_FORM:
				return $this->rawStringResponse("not-valid");
			case BaseController::$NOT_POST:
				$result = new ViewModel();
				$result->setTerminal(true);
				$result->setVariables(array(
						'form' => $form
				));
				return $result;
			case BaseController::$VALID_FORM:
				$data = $form->getData();
				
				$user = $this->getEm()->getRepository('\TH\ZfUser\Entity\UserAccount')->findOneBy(array('email' => $data['email'], 'deleted' => false));
				if ($user)
				{
					if ($user->isConfirmationExpired(7))
					{ // clean up the user, such that the e-mail addres is freed
						$user->softDelete();
						$this->getEm()->flush();
					} else
						return $this->rawStringResponse("duplicate-" . ($user->getKey() == null ? "confirmed" : "unconfirmed"));
				}
				
				if (empty($data['firstname']) && empty($data['lastname']))
				{
						$arr = explode("@", $data['email'], 2);
						$emailname = $arr[0];
						$data['firstname'] = $emailname;
						$data['lastname'] = '';
				}

				// account data
				$accountdata = array(
						'email' => $data['email'],
						'password' => \TH\ZfUser\Rights\AuthAdapter::create_hash($data['password']),
						'verified' => false
				);

				// profile data
				$profiledata = array(
						'firstname' => $data['firstname'],
						'lastname' => $data['lastname'],
				);

				$user = new \TH\ZfUser\Entity\UserAccount($accountdata);
				$this->getEm()->persist($user);

				$profile = $this->getEm()->resolveEntity('\TH\ZfUser\Entity\UserProfile', array($profiledata));
				$this->getEm()->persist($profile);

				$user->storeProfile($profile);

				// Now sent an e-mail with confirmation link
				$user->setKey(\Base\Util\Generator::generateKey(40));
				$user->setConfirmRequest(new \DateTime(date('Y-m-d H:i:s')));

				$this->sendConfirmationMail($user);

				$this->getEm()->flush();

				return $this->rawStringResponse("done");
		}
	}

    public function loginProvidersAction()
    {
        $active_providers = $this->LoginApi()->getAvailableProviders();

        $result = new ViewModel();
        $result->setTerminal(true);
        $result->setVariables(array(
            'active_providers' => $active_providers,
        ));
        return $result;
    }

    /**
     * A user gets an e-mail upon deleting his account.
     * A link with a key is provided to restore that account.
     */
    public function reactivateAccountAction()
    {
        $config = $this->getConfig();

        if ($this->getUserLib()->getUser() != null)
        {
            // already logged in
            return $this->redirect()->toUrl('/');
        }

        $key = $this->params()->fromQuery('key');

        if ($key == null)
        {
            return $this->redirect()->toUrl('/');
        }

        $user = $this->getEm()->getRepository('\TH\ZfUser\Entity\UserAccount')->findOneBy(array('restoreKey' => $key, 'deleted'=>false));
        if (!$user)
        {
            $this->flashMessenger()->addMessage($config['TH']['ZfUser']['commonmsg'][$this->getLang()]['already_restored']);
            return $this->redirect()->toUrl('/');
        }

        $this->flashMessenger()->addMessage($config['TH']['ZfUser']['commonmsg'][$this->getLang()]['deleted']);
        $user->setDeleted(false);
        $user->setRestoreKey(null);
        $user->setDeletedDate(null);
        $this->getEm()->flush();

        return $this->redirect()->toUrl('/');
    }

    public function deleteConfirmAction()
    {
        $config = $this->getConfig();
        $key = $this->params()->fromQuery('key');

        if ($key == null)
        {
            return $this->redirect()->toUrl('/');
        }

        $user = $this->getEm()->getRepository('\TH\ZfUser\Entity\UserAccount')->findOneBy(array('restoreKey' => $key));
        if (!$user)
        {
            $this->flashMessenger()->addMessage($config['TH']['ZfUser']['commonmsg'][$this->getLang()]['already_restored']);
            return $this->redirect()->toUrl('/');
        }

        // remove user credentials so he can register again on a later time
        $accesses = $user->getAccesses();
        foreach ($accesses as $access)
        {
            $this->getEm()->remove($access);
        }

        $pages = $this->getEm()->getRepository('\Application\Entity\Page')->findBy(
                array(
                    'user' => $user,
                )
        );

        foreach ($pages as $page)
        {
            $page->setDeletedAt(new \DateTime(date('Y-m-d H:i:s')));
            $page->setStatus('deleted');
        }

        $user->storeHybridauthSession(NULL);
        $user->setEmail(NULL);

        $user->softDelete();
        $this->getEm()->flush();

        $this->flashMessenger()->addMessage($config['TH']['ZfUser']['commonmsg'][$this->getLang()]['deleted']);
        return $this->redirect()->toUrl('/');
    }

    public function confirmSignupAction()
    {
        $config = $this->getConfig();
		$msgs = $config['TH']['ZfUser']['commonmsg'][$this->getLang()];

        if ($this->getUserLib()->getUser() != null)
        {
            // already logged in
            return $this->redirect()->toUrl('/');
        }

        $key = $this->params()->fromQuery('key');

        if ($key == null)
        {
            return $this->redirect()->toUrl('/');
        }


        $user = $this->getEm()->getRepository('\TH\ZfUser\Entity\UserAccount')->findOneBy(array('confirmKey' => $key, 'deleted' => false));
        if (!$user)
        {
            $this->flashMessenger()->addMessage($msgs['already_confirmed']);
            return $this->redirect()->toUrl('/');
        }

        if ($user->isConfirmationExpired())
        {
					$this->flashMessenger()->addMessage($msgs['request_expired']);

					// remove user from database so he can register again.
					$user->softDelete();
					$this->getEm()->flush();

					return $this->redirect()->toUrl('/');
        }

        // ok, done
        $this->flashMessenger()->addMessage($msgs['validated']);
        $user->setVerified(true);
        $user->setKey(null);
        $user->setConfirmRequest(null);
        $this->getEm()->flush();

        return $this->redirect()->toUrl('/user/login');
    }

    /**
     * Sent user an e-mail with instructions and link to reset password.
     */
    public function forgotpasswordAction()
    {
        $config = $this->getConfig();
        $site_settings = $config['site_settings'];

        $form = new \TH\ZfUser\Form\ResetPasswordForm($this->getServiceLocator(), 'reset');

        $req = $this->getRequest();

        if ($req->isPost())
        {
            $data = json_decode(file_get_contents("php://input"), TRUE);
            $form->setData($data);

            if ($form->isValid())
            {
                $user = $this->getEm()->getRepository('\TH\ZfUser\Entity\UserAccount')->findOneBy(array('email' => $form->get('email')->getValue(), 'deleted' => false));

                if ($user)
                {
                    if ($user->getPassword() == '-')
                    {
                        // @TODO ?
                    }
                    else
                    {
                        $data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);

                        $user->setKey(\Base\Util\Generator::generateKey(40));
                        $user->setConfirmRequest(new \DateTime(date('Y-m-d H:i:s')));

                        $sm = $this->getServiceLocator();
                        $mail = $sm->get('SxMail\Service\SxMail');
                        $sxMail = $mail->prepare();
                        $viewModel = new ViewModel(
                                array(
                            'user' => $user,
                            'title' => 'Password reset information'
                                )
                        );

                        // Get the language
                        $translator = $this->getServiceLocator()->get('translator');

                        $subject = sprintf($translator->translate('Reset your password'));

                        $viewModel->setVariables(array(
                            'text' => $translator->translate('Click here to reset your Remembr. password. The link will expire in 7 days.'),
                            'resettext' => $translator->translate('Reset your password'),
                            'greetz' => $translator->translate('Kind regards,'),
                        ));


                        $viewModel->setTemplate('th/account/resetpassMail.twig');
                        $message = $sxMail->compose($viewModel);
                        $message->setFrom($site_settings['noreply'], $site_settings['sitename'])
                                ->setTo($user->getEmail())
                                ->setSubject($subject);

                        $sxMail->send($message);
                        $this->getEm()->flush();
                    }
                    echo "done";
                    die;
                }
                else
                {
                    echo "no-user";
                    die;
                }
            }
            echo "no-user";
            die;
        }

        $result = new ViewModel();
        $result->setTerminal(true);
        $result->setVariables(array(
            'form' => $form,
        ));
        return $result;
    }

    /**
     * Get and process new password.
     */
    public function forgotpassword2Action()
    {

        if ($this->getUserLib()->getUser() != null)
        {
            // already logged in
            $this->toHomePage();
        }

        $req = $this->getRequest();
        if ($req->isPost())
        {
            $data = $req->getPost();
            $key = isset($data['key']) ? $data['key'] : null;
        }
        else
        {
            $key = $this->params('formid');
        }

        if ($key == null)
        {
            $this->toHomePage();
        }

		$config = $this->getConfig();
		$msgs = $config['TH']['ZfUser']['commonmsg'][$this->getLang()];


        $user = $this->getEm()->getRepository('\TH\ZfUser\Entity\UserAccount')->findOneBy(array('confirmKey' => $key, 'deleted' => false));
        if (!$user)
        {
            $this->flashMessenger()->addMessage($msgs['already_changed']);
            return $this->toHomePage();
        }

        // Check for expired request.
        $now = new \DateTime(date('Y-m-d H:i:s'));
        $request = $user->getConfirmRequest();

        if ($request->diff($now)->format("%a") > 6)
        {
            $this->flashMessenger()->addMessage($msgs['expired']);
            $user->setKey(null);
            $user->setConfirmRequest(null);

            $this->getEm()->flush();

            return $this->toHomePage();
        }

        $form = new \TH\ZfUser\Form\ConfirmForm('confirm');

        if ($req->isPost())
        {
            $form->setData($data);
            if ($form->isValid())
            {
                $user->setPassword(\TH\ZfUser\Rights\AuthAdapter::create_hash($data['password']));
                $user->setKey(null);
                $user->setConfirmRequest(null);
                $this->getEm()->flush();
                $this->flashMessenger()->addMessage($msgs['password_saved']);
                return $this->toHomePage();
            }
        }

        $view = $this->getView()->setVariables(array(
            'key' => $key
        ));

        return $view;
    }

    /**
     * Logout this user from zend authentication.
     */
    public function logoutAction()
    {
        $service = new \Zend\Authentication\AuthenticationService();
        $service->clearIdentity();

        $this->getSessionManager()->forgetMe();

        echo "done";
        die;
    }

    /**
     * update user profile
     *
     * Validation takes place in angular code.
     */
    public function updateUserAction()
    {
        $user = $this->getUserLib()->getUser();
        if (!$user)
        {
            $this->toHomePage();
        }

        $req = $this->getRequest();
        if ($req->isPost())
        {
            $rawPost = json_decode(file_get_contents("php://input"), TRUE) ? : $req->getPost();

            // update profile
            $user->exchangeArray($rawPost);
            $this->getEm()->flush();

            echo "done";
            die;
        }
    }

    /**
     *
     *
     *
     * Validation takes place in angular code.
     */
    public function updatePasswordAction()
    {
        $user = $this->getUserLib()->getUser();
        if (!$user)
        {
            $this->toHomePage();
        }

        $req = $this->getRequest();
        if ($req->isPost())
        {
            $rawPost = json_decode(file_get_contents("php://input"), TRUE) ? : $req->getPost();

            $user->setPassword(\Auth\Rights\AuthAdapter::create_hash($rawPost['password']));
            $this->getEm()->flush();

            echo "done";
            die;
        }
    }

    public function updateEmailAction()
    {
        $user = $this->getUserLib()->getUser();
        if (!$user)
        {
            $this->toHomePage();
        }

        $req = $this->getRequest();
        if ($req->isPost())
        {
            $rawPost = json_decode(file_get_contents("php://input"), TRUE) ? : $req->getPost();

            // check if this e-mail is in use already
            if ($this->getEm()->getRepository('TH\ZfUser\Entity\UserAccount')->findOneBy(array('email' => $rawPost['email'])))
            {
                echo "error";
                die;
            }

            $user->setEmail($rawPost['email']);
            $this->getEm()->flush();

            echo "done";
            die;
        }
    }

    /**
     * Confirm signup.
     * @return \Zend\View\Model\ViewModel
     */
    public function confirmAction()
    {
        if ($this->getUserLib()->getUser() != null)
        {
            return $this->redirect()->toUrl('/');
        }

        $req = $this->getRequest();
        if ($req->isPost())
        {
            $data = $req->getPost();
            $key = isset($data['key']) ? $data['key'] : null;
        }
        else
        {
            $key = $this->params()->fromQuery('key');
        }
        if ($key == null)
        {
            return $this->redirect()->toUrl('/');
        }
        $user = $this->getEm()->getRepository('\TH\ZfUser\Entity\UserAccount')->findOneBy(array('confirmKey' => $key, 'deleted' => false));
        if (!$user)
        {
            return $this->redirect()->toUrl('/');
        }
        $form = new \Application\Forms\ConfirmForm('confirm');

        if ($req->isPost())
        {
            $form->setData($data);
            if ($form->isValid())
            {
                $user->setPassword(\Auth\Rights\AuthAdapter::create_hash($data['password']));
                $user->setKey(null);
                $user->setConfirmRequest(null);
                $this->getEm()->flush();
                $view = new \Zend\View\Model\ViewModel();
                $view->setTemplate('application/account/confirm-finished');
                return $view;
            }
        }
        else
        {
            $form->setData(array('key' => $key));
        }

        return array(
            'form' => $form
        );
    }

    public function alertSettingsAction()
    {

    }

    public function yourFiltersAction()
    {

    }

    protected function getUserLib()
    {
        if (!$this->userlib)
            $this->userlib = $this->getServiceLocator()->get('TH\ZfUser\Library\UserLib');

        return $this->userlib;
    }
}

