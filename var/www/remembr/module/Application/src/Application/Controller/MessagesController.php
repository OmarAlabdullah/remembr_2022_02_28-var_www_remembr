<?php

namespace Application\Controller;

use Base\Controller\BaseController;
use Application\Entity\MessageCentreInbox;
use Application\Entity\MessageCentreOutbox;
use Application\Entity\MessageCentreMessage;
use Zend\View\Model\ViewModel;

class MessagesController extends BaseController
{

	/**
	 * @TODO Maybe we can simplify the names of actions, we already know it's
	 * the message controller (and it's int he url), so getNewMessage is a bit
	 * redundant; getNew would suffice, or just get with optional parameters
	 * to filter what to get.
	 * Trade-off: lot's of extra work changing it everywhere vs brevity and consistent
	 * interface for multiple controllers (i.e. content, user, messages, notifications etc
	 * would all have get, delete etc.
	 */

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
			case 'setreaddate':
			case 'delete':
			case 'reply':
			case 'new':
				if (!$this->getRequest()->isPost())
    {
					throw new \Exception('not a post request', 405);
				}
				// intentional fallthrough
			case 'getunreadmessagesnumber':
			case 'getnewmessages':
			case 'getinbox':
			case 'getoutbox':
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

    public function setReadDateAction() //@TODO maybe make it consistent with notifications, and change to setread.
    {
        $req = $this->getRequest();

		$data = json_decode($req->getContent(), TRUE) ? : $req->getPost();

        $id = $data['id'];
        $message = $this->getEm()->find('\Application\Entity\MessageCentreInbox', $id);

        if ($message && $this->getUser() === $message->getTo())
        {
            $readdate = new \DateTime();
            $message->setReadDate(new \DateTime());
            $this->getEm()->flush();
            echo $readdate->format('d-m-Y H:i');
            die;
        }

		//@TODO throw exception instead.
        echo "error";
        die;
    }

    /**
     * Delete message from inbox or outbox for user only.
     */
     public function deleteAction()
    {
        $req = $this->getRequest();
		$data = json_decode($req->getContent(), TRUE) ? : $req->getPost();
		$id = $data['id'];
		$box = $data['box'];

		if ($box === 'in')
		{
			$message = $this->getEm()->find('\Application\Entity\MessageCentreInbox', $id);
			$msgUser = $message->getTo();
		}
		else
		{
			$message = $this->getEm()->find('\Application\Entity\MessageCentreOutbox', $id);
			$msgUser = $message->getFrom();
		}

		if ($message && $this->getUser() == $msgUser)
		{
			$message->setDeleted(true);
			$this->getEm()->flush();
			echo "done";
			die;
		}
    }

    public function getUnreadMessagesNumberAction()
    {
        $user = $this->getUser();
        $view = $this->getView();

//        $qb = $this->getEm()->getRepository('Application\Entity\MessageCentreInbox')->createQueryBuilder('n');
//        $messages = (
//            $qb->where($qb->expr()->eq('to', $user))
//                ->andWhere($qb->expr()->eq('deleted', false))
//                ->andWhere($qb->expr()->isNull('readDate'))
//                ->orWhere($qb->expr()->eq('newmsg', true))
//                ->getQuery()->getResult()
//        );
        $messages = $this->getEm()->getRepository('Application\Entity\MessageCentreInbox')->findBy(
			array(
				'to' => $user,
				'deleted' => false,
				'readDate' => null
			)
        );

        $view->setVariables(
			array(
				'number' => count($messages)
			)
        );
        return $view;
    }

   public function getNewMessagesAction()
    {
        $user = $this->getUser();
        $view = $this->getView();

        $messages = $this->getEm()->getRepository('Application\Entity\MessageCentreInbox')->findBy(
			array(
				'to' => $user,
				'deleted' => false,
				//'newmsg' => true
				'readDate' => null
			)
        );

        $msgArr = array();
        foreach ($messages as $msg)
        {
            $outMsg = $this->getEm()->getRepository('Application\Entity\MessageCentreOutbox')->findOneBy(array('message' => $msg->getMessage()->getId()));
			$profile = $outMsg->getFrom()->getProfile();
			$actualmsg = $msg->getMessage();

            $msgArr[] = array(
                'id'			=> $msg->getId(),
                'message_id'	=> $actualmsg->getId(),
                'title'			=> $actualmsg->getTitle(),
                'senddate'		=> $actualmsg->getSendDate()->format(DATE_ISO8601),
                'content'		=> $actualmsg->getContent(),
                'readdate'		=> $msg->getReadDate() == null ? '' : $msg->getReadDate()->format(DATE_ISO8601),
                'from_firstname'=> $profile->getFirstName(),
                'from_lastname' => $profile->getLastName(),
                'from_photo'	=> $profile->getPhotoid() ?: '/images/user-icon-large.png',
                'new'			=> $msg->getReadDate() == null,
				'extra' => $msg->getMessage()->getExtra()
            );

            $msg->setNewmsg(false);
            $this->getEm()->flush();
        }


        $view->setVariables(
                array(
                    'messages' => $msgArr
                )
        );
        return $view;
    }

    /**
     * Get all inbox messages for this user
     */
     public function getInboxAction()
    {
			$user = $this->getUser();
			$view = $this->getView();

			// @TODO: write smarter query
			$msgArr = array();
			$messages = $this->getEm()->getRepository('Application\Entity\MessageCentreInbox')->findBy(
				array(
					'to' => $user,
					'deleted' => false,
					//'readDate' => null
				)
			);

			foreach ($messages as $msg)
			{
				$outMsg = $this->getEm()->getRepository('Application\Entity\MessageCentreOutbox')->findOneBy(array('message' => $msg->getMessage()->getId()));

				$r = array(
					'id' => $msg->getId(),
					'message_id' => $msg->getMessage()->getId(),
					'title' => $msg->getMessage()->getTitle(),
					'senddate' => $msg->getMessage()->getSendDate()->format(DATE_ISO8601),
					'content' => $msg->getMessage()->getContent(),
					'readdate' => $msg->getReadDate() == null ? '' : $msg->getReadDate()->format(DATE_ISO8601),
					'new' => $msg->getReadDate() == null ? true : false,
					'extra' => $msg->getMessage()->getExtra()
				);
                
                if ($outMsg->getFrom()->getProfile()) {
					$r['from_firstname'] = $outMsg->getFrom()->getProfile()->getFirstName();
					$r['from_lastname']  = $outMsg->getFrom()->getProfile()->getLastName();
					$r['from_photo']     = $outMsg->getFrom()->getProfile()->getPhotoid() !== null ? $outMsg->getFrom()->getProfile()->getPhotoid() : '/images/user-icon-large.png';
                } else {
					$r['from_firstname'] = '-';
					$r['from_lastname']  = '-';
					$r['from_photo']     = '/images/user-icon-large.png';
                }
                
                $msgArr[] = $r;
			}
			
			$view->setVariables(
				array(
					'messages' => $msgArr
				)
			);
			return $view;
    }

    /**
     * Get all outbox messages for this user
     */
  public function getOutboxAction()
    {
        $user = $this->getUser();
        $view = $this->getView();

        // @TODO: write smarter query
        $msgArr = array();
        $messages = $this->getEm()->getRepository('Application\Entity\MessageCentreOutbox')->findBy(
                array(
                    'from' => $user,
                    'deleted' => false
                )
        );

        foreach ($messages as $msg)
        {
            $inMsg = $this->getEm()->getRepository('Application\Entity\MessageCentreInbox')->findOneBy(array('message' => $msg->getMessage()->getId()));

            if (is_object($inMsg))  // some old hard deleted users where in my dB, so i check for an existing object tp prevent errors
            {
                $r = array(
                    'id' => $msg->getId(),
                    'message_id' => $msg->getMessage()->getId(),
                    'title' => $msg->getMessage()->getTitle(),
                    'senddate' => $msg->getMessage()->getSendDate()->format(DATE_ISO8601),
                    'content' => $msg->getMessage()->getContent(),
                    'extra' => $msg->getMessage()->getExtra()
                );
                
                if ($inMsg->getTo()->getProfile()) {
					$r['to_firstname'] = $inMsg->getTo()->getProfile()->getFirstName();
					$r['to_lastname']  = $inMsg->getTo()->getProfile()->getLastName();
					$r['to_photo']     = $inMsg->getTo()->getProfile()->getPhotoid() !== null ? $inMsg->getTo()->getProfile()->getPhotoid() : '/images/user-icon-large.png';
                } else {
					$r['to_firstname'] = '-';
					$r['to_lastname']  = '-';
					$r['to_photo']     = '/images/user-icon-large.png';
                }
                
                $msgArr[] = $r;
            }
        }

        /*
          $query = $this->getEm()->createQuery('
          SELECT * FROM Application\Entity\MessageCentreOutbox ob
          LEFT JOIN Application\Entity\MessageCentreInbox ib
          ON ob.message_id = ib.message_id
          WHERE ob.from_id = ?1
          AND ob.deleted = ?2
          ');

          $query->setParameter('1', $user->getId());
          $query->setParameter('2', false);

          $result = $query->getArrayResult();
         */

        $view->setVariables(
                array(
                    'messages' => $msgArr
                )
        );

        return $view;
    }

	/**
	 * @TODO, there is a lot of overlap between reply and new; the common functionality might be split off as function.
	 */
    public function replyAction()
    {
        $req = $this->getRequest();
		$data = json_decode($req->getContent(), TRUE) ? : $req->getPost();

		$newMsgData = array(
			'title' => $data['title'],
			'content' => $data['content'],
			'senddate' => new \DateTime()
		);

		// create new message in messageCentreMessage
		$newMsg = new MessageCentreMessage($newMsgData);
		$this->getEm()->persist($newMsg);

		// insert this message (message) in outbox for this user (from)
		$newOut = new MessageCentreOutbox();
		$newOut->setFrom($this->getUser());
		$newOut->setMessage($newMsg);
		$this->getEm()->persist($newOut);

		// insert to (get from outbox for old message)
		$orig = $this->getEm()->getRepository('Application\Entity\MessageCentreOutbox')->findOneBy(array('message' => $data['message_id']));
		$to = $orig->getFrom();

		$newIn = new MessageCentreInbox();
		$newIn->setTo($to);
		$newIn->setMessage($newMsg);
		$this->getEm()->persist($newIn);

		$inboxMsg = $this->getEm()->find('\Application\Entity\MessageCentreInbox', $data['id']);
		$inboxMsg->setAnswered(true);

		$this->getEm()->flush();

		echo "done";
		die;
    }

    public function newAction()
    {
        $req = $this->getRequest();
		$data = json_decode($req->getContent(), TRUE) ? : $req->getPost();

		$newMsgData = array(
			'title' => $data['title'],
			'content' => $data['content'],
			'senddate' => new \DateTime()
		);
		if ($data['extra'])
		{
			$newMsgData['extra'] = $data['extra'];
		}

		// create new message in messageCentreMessage
		$newMsg = new MessageCentreMessage($newMsgData);
		$this->getEm()->persist($newMsg);

		// insert this message (message) in outbox for this user (from)
		$newOut = new MessageCentreOutbox();
		$newOut->setFrom($this->getUser());
		$newOut->setMessage($newMsg);
		$this->getEm()->persist($newOut);

		// insert to
		$toProfile = $this->getEm()->find('\Application\Entity\UserProfile', $data['id']);
		$to = $toProfile->getAccount();

		$newIn = new MessageCentreInbox();
		$newIn->setTo($to);
		$newIn->setMessage($newMsg);
		$this->getEm()->persist($newIn);

		$this->getEm()->flush();

		// In case fo errors exceptions should be thrown, which are handled as http-error on client side.
		return $this->getView()->setVariable('success',true);
    }
}

