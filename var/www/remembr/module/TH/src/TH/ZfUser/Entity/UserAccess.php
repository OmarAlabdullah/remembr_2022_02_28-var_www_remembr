<?php

namespace TH\ZfUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserAccess
 *
 * @ORM\Entity
 * @ORM\Table(name="userAccess")
 * @property string $auth_id
 * @property string $auth_token
 * @property string $auth_secret
 * @property string $provider
 * @property datetime $created
 */
class UserAccess
{

    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="TH\ZfUser\Entity\UserAccount", inversedBy="accesses")
     */
    protected $account;

    /**
     * @ORM\Column(type="string");
     */
    protected $provider;

    /**
     * @ORM\Column(type="string")
     */
    protected $auth_id;

    /**
     * @ORM\Column(type="string")
     */
    protected $auth_token;

    /**
     * @ORM\Column(type="string")
     */
    protected $auth_secret;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;


    // Following fields are used by the Phpleague\oauth implementation.
    /**
     * @ORM\Column(type="string")
     */
    protected $oauth_json;
    /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function __construct($user, $data = array())
    {
        $this->account = $user;
        $this->provider = $data['provider'];
        $this->auth_id = $data['auth_id'];
        $this->auth_secret = $data['auth_secret'];
        $this->auth_token = $data['access_token'];
        $this->oauth_json = $data['oauth_json'];
        $this->created = new \DateTime(date('Y-m-d H:i:s'));
    }
    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        $this->provider = $data['provider'];
        $this->auth_id = $data['auth_id'];
        $this->auth_secret = $data['auth_secret'];
        $this->auth_token = $data['access_token'];
        $this->oauth_json = $data['oauth_json'];
        $this->created = new \DateTime(date('Y-m-d H:i:s'));
    }

    public function setAccessToken($access_token)
    {
        $this->auth_token = $access_token;
    }

    public function getAccessToken()
    {
        return $this->auth_token;
    }

    public function getUser()
    {
        return $this->account;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getCreated()
    {
        return $this->created;
    }
    public function setOauthJson($json) {
        $this->oauth_json = $json;
    }

    public function getOauthJson() {
        return $this->oauth_json;
    }
}