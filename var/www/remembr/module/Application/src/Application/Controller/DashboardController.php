<?php

namespace Application\Controller;

use Base\Controller\BaseController;
use Application\Entity\UserDashboardSettings;
use Zend\View\Model\ViewModel;

class DashboardController extends BaseController
{

    public function checkAccess($action)
    {
        switch ($action)
        {
            case 'index' :
            case 'email' :
            case 'landingpopup' :
			case 'landingpage':
            case 'password': // purely templates
            if ($this->params('format') == 'json')
            {
                throw new \Exception('format not available', 400);
            }
            case 'deleteaccount':
                return true;

            // intentional fallthrough
            case 'settings': // possibly usefull as json as well ?!
                return true;

            case 'fileupload': // everything below is json only
                if (!$this->getRequest()->isPost())
                {
                    throw new \Exception('not a post request', 405);
                }
            // intentional fallthrough
            case 'getsharedmemories':
            case 'getsettings':
            case 'savesettings':

                if (!$this->getUser())
                {
                    throw new \Exception('please log in', 401);
                }
            // intentional fallthrough
            case 'socialmedia':
                if ($this->params('format') != 'json')
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

    public function settingsAction()
    {
        $active_providers = $this->LoginApi()->getAvailableProviders();
        $connected = $this->loginApi()->getLoggedInProviders();

        $view = $this->getView();

        $view->setVariables(
                array(
                    'email' => $this->loginApi()->getUserEmail(),
                    'active_providers' => $active_providers,
                    'connected' => $connected
                )
        );

        return $view;
    }

    public function passwordAction()
    {
        return $this->getView();
    }

    public function emailAction()
    {
        return $this->getView();
    }

    public function landingPopupAction()
    {
        return $this->getView();
    }

    public function getSharedMemoriesAction()
    {
        $view = $this->getView();

        $memarr = array();
        $memories = $this->getMemoriesUser();

        foreach ($memories as $memory)
        {
            $memarr[] = array(
                'id' => $memory->getId(),
                'firstname' => $memory->getPage()->getFirstname(),
                'lastname' => $memory->getPage()->getLastname(),
                'creationdate' => $memory->getCreationDate()->format('d-m-Y H:i'),
                'type' => $memory->getType(),
                'url' => $memory->getPage()->getUrl()
            );
        }

        // sorting handled by getMemoriesUser
        // @TODO maybe consider multiple sortings? e.g. recent-to-old

        $view->setVariables(array(
            'memories' => $memarr
        ));

        return $view;
    }

    public function getSettingsAction()
    {
        $view = $this->getView();
        $user = $this->getUser();
        if ($settings = $this->getEm()->getRepository('Application\Entity\UserDashboardSettings')->findOneBy(array('user' => $user)))
        {

            $view->setVariables(array(
                'receivePageMessages' => $settings->getReceivePageMessages(),
                'receiveCommentMessages' => $settings->getReceiveCommentMessages(),
                'receivePrivateMessages' => $settings->getReceivePrivateMessages(),
                'receiveUpdates' => $settings->getReceiveUpdates(),
                'receiveTips' => $settings->getReceiveTips(),
                'mailFrequency' => $settings->getMailFrequency()
            ));
        }

        return $view;
    }

    public function deleteAccountAction()
    {
        $em = $this->getEm();
        $user = $this->getUser();
        $tomail = $user->getEmail();

        // set restore key
        $user->setRestoreKey(\Base\Util\Generator::generateKey(40));

        $em->flush();

        // sent confirmation mail
        $config = $this->getServiceLocator()->get('Config');
        $site_settings = $config['site_settings'];
        $mailer = $this->getServiceLocator()->get('SxMail\Service\SxMail');
        $sxMail = $mailer->prepare();

        $viewModel = new ViewModel(array('user' => $user));
        $viewModel->setTemplate('mailtemplates/removedaccount.twig');

        $translator = $this->getServiceLocator()->get('translator');
        $subject = sprintf($translator->translate('Confirm removal Remembr. account'));

        $message = $sxMail->compose($viewModel);
        $message->setFrom($site_settings['noreply'], $site_settings['sitename'])
                ->setReplyTo($site_settings['noreply'])
                ->setSubject($subject)
                ->setTo($tomail);

        $sxMail->send($message);

        echo "done";
        die;
    }

    protected function getMemoriesUser()
    {
        if ($user = $this->getUser())
        {
            return $this->getEm()->createQuery('SELECT m FROM Application\Entity\Memory m JOIN m.page p WHERE m.user = :user ORDER BY p.lastname')
                            ->setParameter('user', $user)->getResult();
        }
        return array();
    }

    public function saveSettingsAction()
    {
        $user = $this->getUser();
        $em = $this->getEm();

        $req = $this->getRequest();
        if ($req->isPost())
        {
            $data = json_decode($req->getContent(), TRUE) ? : $req->getPost();

            if ($settings = $em->getRepository('Application\Entity\UserDashboardSettings')->findOneBy(array('user' => $user)))
            {
                // update
                $settings->exchangeArray($data);
            }
            else
            {
                // new
                $settings = new UserDashboardSettings();
                $settings->setUser($user);
                $settings->exchangeArray($data);
                $em->persist($settings);
            }

            $em->flush();

            echo "done";
            die;
        }
    }

    /**
     * Return
     * @return \Zend\View\Model\JsonModel
     */
    public function socialMediaAction() // @TODO this gives pretty similar information to settings, can these be consolidated into one?
    {
        $active_providers = $this->LoginApi()->getAvailableProviders();
        $connected = $this->loginApi()->getLoggedInProviders();

        // remove default provider because we do not need it
        if (($key = array_search("default", $connected)) !== false)
        {
            unset($connected[$key]);
        }

        $non_connected = array();
        foreach ($active_providers as $provider)
        {   // all providers
            if (!in_array($provider, $connected))
            {
                $non_connected[] = $provider;
            }
        }

        $view = $this->getView();

        $view->setVariables(array(
            'non_connected' => $non_connected,
            'connected' => $connected,
            'profilephoto' => $this->loginApi()->getProfilePhoto(),
        ));

        return $view;
    }

    public function landingPageAction()
    {
        $this->view = $this->getView();
        $lang = $this->getLang();

        // add video
        switch ($lang)
        {
            case 'nl' :
            case 'nl-be' :
                $this->view->setVariable('vid', '103513509');
                break;

            case 'en':
            // intentional fallthrough
            default :
                $this->view->setVariable('vid', '103512311');
                break;
        }

        $this->view->setTemplate('template/home.twig');
        return $this->view;
    }


    // @TODO consolidate this with other file upload functionality. Code duplication is bad.
    public function fileUploadAction()
    {
        $req = $this->getRequest();
        $view = $this->getView();
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
        $filter = new \Zend\Filter\File\RenameUpload(array(
            'target' => "./data/uploads/$url$ext",
            'overwrite' => true
        ));
        $res = $filter->filter($files['file']);

        if ($res)
        {
            // save photo
            $user = $this->getUser() ? : null;
            if ($user)
            {
                $em = $this->getEm();
                $user->getProfile()->setPhotoid("/uploads/$url$ext");
                $em->flush();
            }

            $view->setVariable('photo', "/uploads/$url$ext");

            // for IE, else it asks to download/save the data...
            // @TODO add to the appropriate controller
            $this->getServiceLocator()->get('Application')->getEventManager()->attach(\Zend\Mvc\MvcEvent::EVENT_RENDER, function($event) {
                        $event->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'text/html');
                    }, -10000);

            return $view;
        }
        else
        {
            $this->getResponse()->setStatusCode(500);
            return $view->setVariable('error', '');
        }
    }

}