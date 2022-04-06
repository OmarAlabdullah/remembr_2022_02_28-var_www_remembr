<?php

namespace Application\Controller;

use Base\Controller\BaseController;
use Application\Entity\Comment;
use Zend\Session\Container;
use Zend\Json\Json;

class CommentController extends BaseController
{
	public function checkAccess($action)
	{
		switch($action)
		{
			case 'list': // public page, or friend
				$mem = $this->getEm()->find('Application\Entity\Memory', $this->params('id'));
				if (empty($mem))
				{
					throw new \Exception('missing parameter', 400);
				}
				$page = $mem->getPage();
				if ($page->getPrivate() &&
					! \Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin)  &&
					! \Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$friend)
					)
				{
					throw new \Exception('page access restricted', 403);
				}

				return true;
			case 'create': // post request and logged in and public page or friend

				if (! $this->getUser())
				{
					throw new \Exception('user not logged in', 403);
				}

				$req = $this->getRequest();
				if (! $req->isPost())
				{
					throw new \Exception('not a post request', 405);
				}

				$input = json_decode($req->getContent());
				if (empty($input->memory))
				{
					throw new \Exception('missing parameter', 400);
				}

				$mem = $this->getEm()->find('\Application\Entity\Memory', $input->memory);
				if (!$mem)
				{
					throw new \Exception('bad parameter value', 400);
				}
				$page = $mem->getPage();
				if ($page->getPrivate() &&
					! \Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin)  &&
					! \Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$friend)
					)
				{
					throw new \Exception('user not allowed to add comment', 403);
				}
				return true;
			case 'deletecomment': // page admin or commenter @TODO
				// handled by function itself.
				return true;
		}

		return false;
	}

    public static function jsonify(Comment $cmt)
    {
        $result = $cmt->getArrayCopy();
        $result['createDate'] = $result['createDate']->format(DATE_ISO8601);
//		$result['userProfile'] = $cmt->getUser() ? $cmt->getUser()->getProfile()->getArrayCopy() : null;
        return $result;
    }

    public function listAction()
    {
        $em = $this->getEm();
		$memid = $em->getReference('Application\Entity\Memory', $this->params('id'));

        return new \Zend\View\Model\JsonModel(
                array_map(
                        function($e) use ($em) {
                            return CommentController::jsonify($e, $em);
                        },
						$this->getEm()->getRepository('Application\Entity\Comment')->findBy(array(
							'memory' => $memid,
							'deletedAt' => null
						)
					)
                )
        );
    }

    public function createAction()
    {
        $req = $this->getRequest();
        $input = json_decode($req->getContent());
        $user = $this->getUserLib()->getUser() ? : null;

        $em = $this->getEm();
        $comment = new Comment();
        $comment->setUser($user);
        $comment->setText($input->text);
        $memory = $em->getReference('\Application\Entity\Memory', $input->memory);
        $comment->setMemory($memory);

        $em->persist($comment);
        $em->flush();

        // notify admins of new content
        $this->notifyAdmin($memory, $comment, $user, $comment->getMemory()->getPage());
        // notify orignal poster of new content
        $this->notifyPoster($memory, $comment, $user, $comment->getMemory()->getPage(), $comment->getMemory()->getUser());

        return new \Zend\View\Model\JsonModel(
                self::jsonify($comment, $em)
        );
    }

    protected function notifyAdmin($memory, $comment, $user, $page)
    {
			// no notifications if sender = page owner
			if ($page->getUser() === $user) return;

			$notification = new \Application\Entity\Notification(array(
				'page' => $page,
				'receiver' => $page->getUser(),
				'sender' => $user,
				'comment' => $comment,
				'memory' => $memory,
				'event'	=> 'shared'
			));

			$this->getEm()->persist($notification);
			$this->getEm()->flush();
    }

	protected function notifyPoster($memory, $comment, $user, $page, $poster)
	{
		// no notifications if poster = page owner || page-owner = poster (notifyAdmin takes care of that)
		if ($poster === $user || $page->getUser() === $poster) return;

		$notification = new \Application\Entity\Notification(array(
			'page' => $page,
			'receiver' => $poster,
			'sender' => $user,
			'comment' => $comment,
			'memory' => $memory,
			'event'	=> 'shared'
		));

		$this->getEm()->persist($notification);
		$this->getEm()->flush();
	}

	/**
	 * @TODO rewrite frontend/backend to use regular json style, for consistency's sake.
	 */
    public function deleteCommentAction()
    {
        $req = $this->getRequest();
        if ($req->isPost())
        {
            $data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);

            $id = $data['id'];

            if ($comment = $this->getEm()->getRepository('\Application\Entity\Comment')->findOneBy(array('id' => $id)))
            {
                $page =  $comment->getMemory()->getPage();
                if ($comment->getUser() == $this->getUser() || \Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin))
                {
                    $this->getEm()->remove($comment);

                    // update 'newnotification' so owner gets signal this is deleted except if the user is the owner
                    if ($notification = $this->getEm()->getRepository('\Application\Entity\Notification')->findOneBy(array('comment' => $comment)))
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