<?php

/**
 *
 */

namespace TH\ZfUser\Library;

use Zend\Session\Container;

class UserLib extends BaseLib
{

    /**
     * Get all providers for this user.
     *
     * @param string $user
     * @return array
     */
    public static function getUserProviders($user)
    {
        $providers = array();

        $useraccesses = $user->getAccesses();

        foreach ($useraccesses as $useraccess)
        {
            $providers[] = $useraccess->getProvider();
        }

        return $providers;
    }

    /**
     * Get user object.
     *
     * A user can be logged in by a social provider or by normal login.
     * Try to find the user one way or the other.
     *
     * @return \TH\ZfUser\Entity\UserAccount
     */
    public function getUser()
    {
        $identity = $this->getIdentity();
        $user_session = new Container('user');

        if ($identity)
        {
            return $this->getEm()->getRepository('TH\ZfUser\Entity\UserAccount')->findOneBy(array('id' => $identity['userId'], 'verified' => '1', 'deleted' => '0'));
        }
        elseif (isset($user_session->user_id) && $user_session->user_id !== null)
        {
             return $this->getEm()->getRepository('TH\ZfUser\Entity\UserAccount')->findOneBy(array('id' => $user_session->user_id, 'verified' => '1', 'deleted' => '0'));
        }

        return false;
    }

    /**
     * Check if we have an logged in user.
     *
     * @return boolean true: we have, false: we have not
     */
    public static function doWeHaveLoggedInUser()
    {
        $user_session = new Container('user');

        return isset($user_session->user_id) && $user_session->user_id != NULL;
    }

    /**
     * Get user_id from logged-in-user.
     *
     * @return int user_id
     */
    public static function currentUserId()
    {
        $user_session = new Container('user');

        return $user_session->user_id;
    }

    /**
     * Find e-mail address in user accounts.
     *
     * @return boolean
     */
    public function emailAddressExists($email)
    {
        $repository = $this->getEm()->getRepository('TH\ZfUser\Entity\UserAccount');

        return $repository->findOneBy(array('email' => $email));
    }

    /**
     * Store new user in dB.
     * Also add @random password. This one will never be used, but can be set by user after login or "forgotten password" mail.
     *
     * @return int: last inserted user_id
     */
    public function storeNewUser($userprofile)
    {
        // Store account data.
        $username = trim($userprofile->firstName . ' ' . $userprofile->lastName);

        if (empty($username) && isset($userprofile->displayName))
        {
            $username = $userprofile->displayName;
            $this->setName($username);
        }

        $password = \Auth\Rights\AuthAdapter::create_hash(base64_encode(openssl_random_pseudo_bytes(12)));


        // account data
        $accountdata = array(
            'email' => $userprofile->email,
            'password' => $password,
        );

        $dateofbirth = date("Y-m-d", mktime(0, 0, 0, $userprofile->birthMonth, $userprofile->birthDay, $userprofile->birthYear));

        if (empty($userprofile->firstName) && empty($userprofile->lastName))
        {
            $arr = explode("@", $userprofile->email, 2);
            $emailname = $arr[0];
            $userprofile->firstName = $emailname;
            $userprofile->lastName = '';
        }

        // profile data
        $profiledata = array(
            'firstname' => !empty($userprofile->firstName) ? $userprofile->firstName : "",
            'lastname' => !empty($userprofile->lastName) ? $userprofile->lastName : "",
            'country' => $userprofile->country,
            'residence' => $userprofile->city,
            'gender' => $userprofile->gender,
            'language' => $userprofile->language,
            'dateofbirth' => $dateofbirth,
        );

        $user = new \TH\ZfUser\Entity\UserAccount($accountdata);
        $this->getEm()->persist($user);

        $profile = $this->getEm()->resolveEntity('\TH\ZfUser\Entity\UserProfile', array($profiledata));
        $this->getEm()->persist($profile);

        $user->storeProfile($profile);
        $user->setVerified(true);

        $this->getEm()->flush();

        return $user;
    }

    protected function setName($value)
    {
        $value = !empty($value) ? $value : "";

        preg_match('/^\s*(\S*)(?:\s+(.*))?\s*$/', $value, $matches);
        $this->userprofile->firstName = $matches[1];
        $this->userprofile->lastName = $matches[2];
    }

    /**
     * Get stored user for this userprofile.
     *
     * The user is identified by its userprofile identifier.
     * The provider is identified by its id AND secret (there could be more apps for one provider).
     */
    public function getUserAccount($id)
    {
        $repository = $this->getEm()->getRepository('TH\ZfUser\Entity\UserAccount');
        $user = $repository->findOneBy(
                array
                    (
                    'id' => $id,
                )
        );

        return $user;
    }

    /**
     * Get (if any) stored user for this userprofile.
     *
     * The user is identified by its userprofile identifier.
     * The provider is identified by its id AND secret (there could be more apps for one provider).
     */
    public function getStoredAccess($userprofile, $provider, $adapter)
    {
        $repository = $this->getEm()->getRepository('TH\ZfUser\Entity\UserAccess');

        $useraccess = $repository->findOneBy(
                array
                    (
                    'auth_id' => $userprofile->identifier,
                    'provider' => $provider,
                    'auth_secret' => !empty($adapter->config['keys']['secret']) ? $adapter->config['keys']['secret'] : '-'
                )
        );

        return $useraccess;
    }

}