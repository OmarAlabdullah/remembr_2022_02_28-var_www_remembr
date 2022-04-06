<?php

namespace Cron\Controller;

use Cron\Controller\CronHandlerController;

class SendCommentMessagesRemindersController extends CronHandlerController
{

    /**
     * Cron scheduler calls the taskAction with a frequentie parameter (direct, dailly, weekly).
     *
     *      * * * * *  public/index.php send-comment-messages-reminders direct  -   every minute
     *      0 0 * * *  public/index.php send-comment-messages-reminders daily   -   every day at 0.00
     *      0 0 * * 5  public/index.php send-comment-messages-reminders weekly  -   every friday at 0.0
     */
    public function taskAction()
    {
        $this->title = _('You have received new comments at Remembr.'); // @TODO find alternative for _()
        $this->subject = _('New comment messages on Remembr.');
        $this->template = 'mailtemplates/comment-messages-reminders.twig';
        $this->logtext = 'SendCommentMessagesReminders';

        $this->init();
        $this->run();
    }

    /**
     * Mark sent messages as "reminded" in database so they will not be sent again.
     *
     * @param array $messages
     */
    protected function setMessagesAsRead(array $messages)
    {
        foreach ($messages as $message)
        {
            $inboxMsg = $this->getEm()->getRepository('\Application\Entity\Notification')
                    ->findOneBy(array('id' => $message->getId()));

            $inboxMsg->setReminded(true);
            $this->getEm()->flush();
        }
    }

    /**
     * Get all users who want to receive messages on a "freq"-ly basis.
     * Process only users who do have messages that are not sent yet.
     *
     * @param string $freq    - direct, daily, weekly
     * @return array
     */
    protected function getUserData()
    {
        $q = $this->getEm()->createQueryBuilder()
                ->select("n")
                ->from("\Application\Entity\Notification", "n")
                ->join("\TH\ZfUser\Entity\UserAccount", "u", "WITH", "n.receiver = u.id")
                ->join("\Application\Entity\UserProfile", "p", "WITH", "p.account = u.id")
                ->join("\Application\Entity\UserDashboardSettings", "s", "WITH", "s.user = u.id")
                ->where("u.verified = 1")
                ->andwhere("u.deleted = 0")
                ->andwhere("s.receivePageMessages = 1")
                ->andwhere("s.mailFrequency = :freq")
                ->andwhere("n.reminded = 0")
                ->andwhere("n.deleted = 0")
                ->andwhere("n.readDate IS NULL")
                ->andwhere("n.comment != false")
                ->setParameter("freq", $this->freq);

        $result = $q->getQuery()->getResult();

        return $this->mapData($result);
    }

}