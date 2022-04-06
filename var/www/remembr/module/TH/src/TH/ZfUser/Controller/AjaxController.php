<?php
/**
 * Some ajax functionality for facebook and twitter.
 */
namespace TH\ZfUser\Controller;

use Base\Controller\BaseController;
use Zend\View\Model\JsonModel;

class AjaxController extends BaseController
{

    protected $loggedOut403 = true;
    protected $facebookfriendsActionLoggedOut = true;
    protected $getFbUidActionLoggedOut = true;
    protected $getUidActionLoggedOut = true;
    protected $getTweetsActionLoggedOut = true;

    // ********************* //
    // ****** COMMON ****** //
    // ********************* //

    /**
     * Return user id for this provider: js check if user is still logged in
     */
    public function getUidAction()
    {
        $provider = $this->getEvent()->getRouteMatch()->getParam('provider');
        $adapter = $this->hybridauth()->getAdapter($provider);
        $user = $adapter->api()->getUser();

        echo $user;
        die;
    }

    // ********************* //
    // ***** FACEBOOK ****** //
    // ********************* //

    /**
     * Get all facebook friends for loggedin user
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function facebookfriendsAction()
    {
        $adapter = $this->hybridauth()->authenticate('facebook');

        $friends = $adapter->getUserContacts();

        $friendsArray = array();
        foreach ($friends as $friend)
        {
            $friendsArray[] = array(
                'uid' => $friend->identifier,
                'name' => $friend->displayName,
                'pict' => $friend->photoURL,
                'profile_url' => $friend->profileURL,
            );
        }

        // sort on name
        $tmp = array();
        foreach ($friendsArray as $a)
        {
            $tmp[] = $a["name"];
        }

        array_multisort($tmp, $friendsArray);

        $result = new JsonModel(array(
            'friends' => $friendsArray,
        ));

        return $result;
    }

    // ********************* //
    // ****** TWITTER ****** //
    // ********************* //

    public function getTweetsAction()
    {
        // get access token and secret for twitter
        $adapter = $this->hybridauth()->authenticate('twitter');
        $twitter_data = $adapter->adapter->getUserActivity('me');

        $result = new JsonModel(array(
            'tweets' => $twitter_data,
        ));

        return $result;
    }

}