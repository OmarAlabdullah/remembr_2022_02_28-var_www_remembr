<?php

namespace Cron\Controller;

use Cron\Controller\CronHandlerController;

class SendPrivateMessagesRemindersController extends CronHandlerController
{

    /**
     * Cron scheduler calls the taskAction with a frequentie parameter (direct, dailly, weekly).
     *
     *      * * * * *  public/index.php send-private-messages-reminders direct  -   every minute
     *      0 0 * * *  public/index.php send-private-messages-reminders daily   -   every day at 0.00
     *      0 0 * * 5  public/index.php send-private-messages-reminders weekly  -   every friday at 0.0
     */
    public function taskAction()
    {
        $this->title = _('You have received new private messages at Remembr.');
        $this->subject = _('New private messages on Remembr.');
        $this->template = 'mailtemplates/private-messages-reminders.twig';
        $this->logtext = 'SendPrivateMessagesReminders';

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
            $inboxMsg = $this->getEm()->find('\Application\Entity\MessageCentreInbox', $message->getId());

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
                ->select("i")
                ->from("\Application\Entity\MessageCentreInbox", "i")
                ->join("\Application\Entity\MessageCentreMessage", "m", "WITH", "m.id = i.message")
                ->join("\Application\Entity\UserDashboardSettings", "s", "WITH", "i.to = s.user")
                ->join("\Application\Entity\UserProfile", "p", "WITH", "s.user = p.account")
                ->join("\TH\ZfUser\Entity\UserAccount", "u", "WITH", "p.account = u.id")
                ->where("u.verified = 1")
                ->andwhere("u.deleted = 0")
                ->andwhere("s.receivePrivateMessages = 1")
                ->andwhere("s.mailFrequency = :freq")
                ->andwhere("i.reminded = 0")
                ->andwhere("i.answered = 0")
                ->andwhere("i.deleted = 0")
                ->orderBy("u.id")
                ->setParameter("freq", $this->freq);


        $result = $q->getQuery()->getResult();

        // map data
        $newArray = array();
        foreach ($result as $record)
        {

            $current = $record->getTo()->getId();
            if (!array_key_exists($current, $newArray))
            {
                $newArray[$current] = array(
                    'user' => $record->getTo(),
                    'messages' => array()
                );
            }
            $newArray[$current]['messages'][] = $record;

        }

        return $newArray;
    }

}