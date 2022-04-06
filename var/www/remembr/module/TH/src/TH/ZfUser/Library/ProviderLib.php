<?php

namespace TH\ZfUser\Library;

class ProviderLib extends BaseLib
{

    /**
     * Check if provider for this user already has credentials.
     * We only allow one set of facebook/twitter/... credentials per user.
     *
     * @return type
     */
    public function existingProviderForUser($user_id, $provider, $adapter)
    {
        $auth_secret = $adapter->config['keys']['secret'];

        $repository = $this->getEm()->getRepository('TH\ZfUser\Entity\UserAccess');

        $validator = new \DoctrineModule\Validator\ObjectExists(array(
            'object_repository' => $repository,
            'fields' => array('account', 'auth_secret', 'provider')
        ));

        return $validator->isValid(array(
                    'account' => $user_id,
                    'auth_secret' => $auth_secret,
                    'provider' => $provider
        ));
    }


}