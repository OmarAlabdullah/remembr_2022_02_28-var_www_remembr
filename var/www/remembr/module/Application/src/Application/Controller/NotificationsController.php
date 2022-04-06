<?php

namespace Application\Controller;

use Base\Controller\BaseController;
use \Application\Entity\Notification;
use Zend\View\Model\ViewModel;

class NotificationsController extends BaseController
{

	public function checkAccess($action)
	{
		switch($action)
		{
			case 'index' :
				if ($this->params('format') == 'json')
				{
					throw new \Exception('format not available', 400);
				}
				return true;
			case 'setread':

				if (!$this->getRequest()->isPost())
				{
					throw new \Exception('not a post request', 405);
				}
				// intentional fallthrough
			case 'setreadall':
			case 'getnewnotifications':
			case 'getnotifications':
			case 'getnotificationshistory':
				if (! $this->getUser())
				{
					throw new \Exception('please log in', 401);
				}
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

    /**
     * A once read notification is also deleted, so it will not be loaded again
     */
    public function setReadAction()
    {
		$data = json_decode($this->getRequest()->getContent(), TRUE) ? : $req->getPost();
		$id = $data['id'];
		$notification = $this->getEm()->find('\Application\Entity\Notification', $id);

		if ($notification && $notification->getReceiver() == $this->getUser())
		{
			$readdate = new \DateTime();
			$notification->setReadDate(new \DateTime());
			$notification->setDeleted(true);
			$this->getEm()->flush();
			echo $readdate->format('d-m-Y H:i');
			die;
		}
    }

    public function setReadAllAction()
    {
        $user = $this->getUser();

        $notifications = $this->getEm()->getRepository('\Application\Entity\Notification')->findBy(
                array(
                    'receiver' => $user,
                    'deleted' => false
                )
        );

        foreach ($notifications as $notification)
        {
            $notification->setNewnotification(false);
            $notification->setDeleted(true);
        }
		$this->getEm()->flush();

        echo "ok";
        die;
    }

	/*
	 * @TODO check if we can't just merge this with getnotification, and use a 'since' parameter to get newer notifications.
	 */
    public function getNewNotificationsAction()
    {
        $user = $this->getUser();
        $view = $this->getView();

		$this->getEm()->getFilters()->disable('soft-deleteable'); // @TODO check if we want/need this.
        $notifications = $this->getEm()->getRepository('\Application\Entity\Notification')->findBy(
			array(
				'receiver' => $user,
				'deleted' => false,
				'newnotification' => true
			)
        );

        $notificationArr = array();
        foreach ($notifications as $notification)
        {
            $notificationArr[] = $this->getOneNotification($notification, true);

            // notification is pulled and in view and so not new anymore
            $notification->setNewnotification(false);
        }
		$this->getEm()->flush();

        $view->setVariables(
                array(
                    'notifications' => $notificationArr
                )
        );

		// because function is used via forward from UserController it's important to reenable.
		$this->getEm()->getFilters()->enable('soft-deleteable'); // @TODO check if we want/need this.
        return $view;
    }

    /**
     * Get all notifications for this user
     */
    public function getNotificationsAction()
    {
        $user = $this->getUser();
        $view = $this->getView();

        $notificationArr = array();
		$this->getEm()->getFilters()->disable('soft-deleteable'); // @TODO check if we want/need this.
        $notifications = $this->getEm()->getRepository('\Application\Entity\Notification')->findBy(
			array(
				'receiver' => $user,
				'deleted' => false
			)
        );

        foreach ($notifications as $notification)
        {
            $notificationArr[] = $this->getOneNotification($notification, $notification->getReadDate() == null ? true : false);
        }

        $view->setVariables(
			array(
				'notifications' => $notificationArr
			)
        );
		// because function is used via forward from UserController it's important to reenable.
		$this->getEm()->getFilters()->enable('soft-deleteable'); // @TODO check if we want/need this.
        return $view;
    }

	/*
	 * @TODO check if we can't just merge this with getnotification, and use somethign like a 'before' and 'includeread' parameter to get historic ones.
     * 20/08/0214: Now all notifications are being displayed, not only history notifications anymore
	 */
    public function getNotificationsHistoryAction()
    {
        $user = $this->getUser();

        $view = $this->getView();

        $notificationArr = array();
		$this->getEm()->getFilters()->disable('soft-deleteable'); // @TODO check if we want/need this.
        $notifications = $this->getEm()->getRepository('\Application\Entity\Notification')->findBy(
                array(
                    'receiver' => $user,
                    //'deleted' => true
                )
        );

        foreach ($notifications as $notification)
        {
            $notificationArr[] = $this->getOneNotification($notification, '');
        }

        $view->setVariables(
                array(
                    'notifications' => $notificationArr
                )
        );
		// because function is used via forward from UserController it's important to reenable.
		$this->getEm()->getFilters()->enable('soft-deleteable'); // @TODO check if we want/need this.
        return $view;
    }

    protected function getOneNotification($notification, $new)
    {
        return array(
			'id'		 => $notification->getId(),
			'createDate' => $notification->getCreateDate()->format('Y-m-d'),
			'createTime' => $notification->getCreateDate()->format(DATE_ISO8601),
			'name'		 => $notification->getPage()->getFirstname() . ' ' . $notification->getPage()->getLastname(),
			'url'		 => $notification->getPage()->getUrl(),
			'memory_id'	 => $notification->getMemory() ? $notification->getMemory()->getId() : null,
			'type'		 => $notification->getComment() ? 'comment' : ($notification->getMemory() ? $notification->getMemory()->getType() : 'page'),
			'senderName' => $notification->getSender() ? $notification->getSender()->getProfile()->getName() : ($notification->getMemory() ? ($notification->getMemory()->getUserName()?: 'Anonymous') : 'Anonymous'), /* @TODO clean up */
			'softdeleted'=> $notification->getComment() ?
								$notification->getComment()->getDeletedAt() :
								($notification->getMemory() ?
									$notification->getMemory()->getDeletedAt() :
									$notification->getPage()->getDeletedAt()),
			'text'		 => $notification->getComment() ? $notification->getComment()->getText() : ( $notification->getMemory() ? $notification->getMemory()->getText() : null),
			'new'		 => $new,
			'event'		 => $notification->getEvent()
		);
    }

}

