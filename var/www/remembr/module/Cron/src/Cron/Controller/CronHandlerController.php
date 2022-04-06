<?php

namespace Cron\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\View\Model\ViewModel;

abstract class CronHandlerController extends AbstractActionController
{
	protected $sxMail;
	protected $viewModel;
	protected $log;
	protected $entityManager;
	protected $freq;
	protected $logtext;
	protected $subject;
	protected $template;
	protected $title;

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
	 * @return Zend\Log
	 */
	protected function getLog()
	{
			if (!$this->log)
			{
					$this->log = $this->getServiceLocator()->get('Zend\Log');
			}
			return $this->log;
	}

	/**
	 * Make sure that we are running in a console.
	 * Get "freq" parameter : direct, daily, weekly.
	 * Set up mailer and viewmodel.
	 */
	protected function init()
	{
			$request = $this->checkConsoleRequest();
			$this->freq = $request->getParam('freq');

			$mailer = $this->getServiceLocator()->get('\SxMail\Service\SxMail');
			$this->sxMail = $mailer->prepare();
			$this->sxMail->setLayout('cron/mail/layout');

			$this->viewModel = new ViewModel();
	}

	/**
	 * Get and process users and messages.
	 */
	protected function run()
	{
			$users = $this->getUserData();

			if (count($users) > 0)
			{
					$this->getLog()->log(\Zend\Log\Logger::INFO, $this->logtext . " " . count($users) . " users: $this->freq");
					$this->process($users);
			}
	}

/**
 * Process users:
 *  - get all messages per user
 *  - sent email with detail on these messages
 *
 * @param array $users All users who want to receive messages on a "freq"-ly basis
 */
protected function process(array $userData)
{
	foreach ($userData as $data)
	{
		$messages = $data['messages'];

		if (count($messages) > 0)
		{
			$translator = $this->getServiceLocator()->get('translator');
			$translator->setLocale($data['user']->getProfile()->getLanguage() == 'nl' ? 'nl_NL' : $data['user']->getProfile()->getLanguage() == 'nl-be' ? 'nl_BE' : 'en_US');

			$this->setViewModelVars($data);

			try
			{
				$this->sendMail($data['user']->getEmail());

				$this->setMessagesAsRead($messages);
			} catch (\Exception $e)
			{
				//var_dump($e->getMessage());
				return 'Exception during cron process: ' . $e->getMessage();
			}
		}
	}
	return;
}

	/**
	 * Set up mail and send mail
	 *
	 * @param string $to        : e-mail address for receiver
	 */
	protected function sendMail($to)
	{
			$config = $this->getServiceLocator()->get('Config');
			$site_settings = $config['site_settings'];

			$this->viewModel->setTemplate($this->template);

			$mail = $this->sxMail->compose($this->viewModel);

			$translator = $this->getServiceLocator()->get('translator');
			$subject = $translator->translate($this->subject);

			$mail->setFrom($site_settings['noreply'], $site_settings['sitename'])
							->setReplyTo($site_settings['noreply'], $site_settings['sitename'])
							->setSubject($subject)
							->setTo($to);
							//->setTo('mark.scheper@target-holding.nl');
			
			$this->sxMail->send($mail);
	}

	/**
	 * Set view model variables
	 *
	 * @param array $messages
	 * @param \TH\ZfUser\Entity\UserAccount $user
	 */
	protected function setViewModelVars(array $data)
	{	
		$this->viewModel->setVariables(array(
			'title' => $this->title,
			'messages' => $data['messages'],
			'number' => count($data['messages']),
			'condolences' => isset($data['condolence']) ? $data['condolence'] : null,
			'photos' => isset($data['photo']) ? $data['photo']: null,
			'videos' => isset($data['video']) ? $data['video']: null,
			'memories' => isset($data['memory']) ? $data['memory']: null,
			'ncondolences' => isset($data['condolence']) ? count($data['condolence']): 0,
			'nphotos' => isset($data['photo']) ? count($data['photo']): 0,
			'nvideos' => isset($data['video']) ? count($data['video']): 0,
			'nmemories' => isset($data['memory']) ? count($data['memory']): 0,
			'firstname' => $data['user']->getProfile()->getFirstName(),
			'lastname' => $data['user']->getProfile()->getLastName(),
			'lang' => $data['user']->getProfile()->getLanguage(),
		));
	}

	/**
	 *  Make sure that we are running in a console and the user has not tricked our
	 *  application into running this action from a public web server.
	 */
	protected function checkConsoleRequest()
	{
			$request = $this->getRequest();

			if (!$request instanceof ConsoleRequest)
			{
					throw new \RuntimeException('You can only use this action from a console!');
			}

			return $request;
	}

	/**
	 * Map all messages to the corresponding user.
	 *
	 * @param array $result
	 * @return array
	 */
	protected function mapData(array $result)
	{
			$newArray = array();
			foreach ($result as $record)
			{
					$current = $record->getReceiver()->getId();
					if (!array_key_exists($current, $newArray))
					{
							$newArray[$current] = array(
									'user' => $record->getReceiver(),
									'page' => $record->getPage(),
									'memory' => array(),
									'photo' => array(),
									'video' => array(),
									'condolence' => array(),
									'messages' => array()
							);
					}
					$newArray[$current][$record->getMemory()->getType()][] = $record;
					$newArray[$current]['messages'][] = $record;
			}
			return $newArray;
	}

	public abstract function taskAction();

	protected abstract function getUserData();

	protected abstract function setMessagesAsRead(array $messages);
}