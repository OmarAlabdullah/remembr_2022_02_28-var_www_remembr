<?php

namespace Application\Controller;

use Base\Controller\BaseController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class AjaxController extends BaseController
{

    public function checkAccess($action)
    {
        switch ($action)
        {
            case 'contact':
            case 'signnewsletter':
            case 'grablanguage':
            default:
                return true;
        }
    }

    public function contactAction()
    {
        $req = $this->getRequest();
        if ($req->isPost())
        {
            $data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);

            $config = $this->getServiceLocator()->get('Config');
            $site_settings = $config['site_settings'];
            $mailer = $this->getServiceLocator()->get('SxMail\Service\SxMail');
            $sxMail = $mailer->prepare();

            $viewModel = new ViewModel(array(
                'text' => $data['comment'],
            ));

            $viewModel->setTemplate('mailtemplates/contact.twig');

            $message = $sxMail->compose($viewModel);
            $message->setFrom($site_settings['noreply'], $site_settings['sitename'])
                    ->setReplyTo($data['email'], $data['name'])
                    ->setSubject("Bericht via de website Remembr.")
                    ->setTo($site_settings['email_to']);

            $sxMail->send($message);

            echo "done";
            die;
        }
    }

    public function signNewsletterAction()
    {
        $req = $this->getRequest();
        if ($req->isPost())
        {
            $data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
            $email = $data['email'];

            if ($this->getEm()->getRepository('\Application\Entity\Newsletter')->findOneBy(array('email' => $email)))
            {
                echo "exists";
                die;
            }
            else
            {
                $newNewsletterSignIn = new \Application\Entity\Newsletter();
                $annotationbuilder = new \Zend\Form\Annotation\AnnotationBuilder();
                $form = $annotationbuilder->createForm($newNewsletterSignIn);

                $form->bind($newNewsletterSignIn);
                $form->setData($data);

                if ($form->isValid())
                {
                    $newNewsletterSignIn->setEmail($email);
                    $this->getEm()->persist($newNewsletterSignIn);
                    $this->getEm()->flush();

                    // sent confirm mail
                    $config = $this->getServiceLocator()->get('Config');
                    $site_settings = $config['site_settings'];
                    $mailer = $this->getServiceLocator()->get('SxMail\Service\SxMail');
                    $sxMail = $mailer->prepare();

					$lang = $this->params('lang');
                    $key = $newNewsletterSignIn->getConfirmkey();
                    $url = $this->url()->fromRoute(
                            'remembr/generic/wildcard', array('controller' => 'user', 'action' => 'confirm-newsletter', 'confirmkey' => $key, 'lang' => $lang), array('force_canonical' => true), 0);

                    $viewModel = new ViewModel();
                    $viewModel->setTemplate('mailtemplates/newsletter.twig')
					          ->setVariables(array( 'url' => $url ));

                    $translator = $this->getServiceLocator()->get('translator');
                    $subject = $translator->translate('Newsletter registration');

                    $message = $sxMail->compose($viewModel);
                    $message->setFrom($site_settings['noreply'], $site_settings['sitename'])
                            ->setReplyTo($site_settings['noreply'])
                            ->setSubject($subject)
                            ->setTo($data['email']);

                    $sxMail->send($message);

                    echo "done";
                    die;
                }
            }
        }

        echo "error";
        die;
    }

    /**
     * Grab the preferred language of the browser client
     */
    public function grabLanguageAction()
    {
        $translator = $this->getServiceLocator()->get('translator');
        echo substr($translator->getLocale(), 0, 2);
        die;
    }

}
