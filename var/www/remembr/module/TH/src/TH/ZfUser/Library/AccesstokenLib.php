<?php

namespace TH\ZfUser\Library;

class AccesstokenLib extends BaseLib
{
    // http://developers.facebook.com/blog/post/2011/05/13/how-to--handle-expired-access-tokens/
    /*
     * Scenarios:
     *
     * The token expires after expires time (2 hours is the default).
     * The user changes her password which invalidates the access token.
     * The user de-authorizes your app.
     * The user logs out of Facebook.
     *
     * To ensure the best experience for your users, your app needs to be prepared
     * to catch errors for the above scenarios. The following PHP handles these errors
     * and retrieve a new access token.
     *
     * When you redirect the user to the auth dialog, the user is not prompted
     * for permissions if the user has already authorized your application.
     * Facebook will return you a valid access token without any user facing dialog.
     * However if the user has de-authorized your application then the user will
     * need to re-authorize your application for you to get the access_token.
     */

    /**
     * Check if access token is still valid for this provider.
     * If not, update access token.
     * (yet only needed for facebook) @TODO: check
     *
     * @param string $provider
     */
    public function validateAccessToken($provider)
    {
        $adapter = $this->getHybridauth()->authenticate($provider);

        switch ($provider)
        {
            case 'facebook':

                $facebook = $adapter->api();
                $access_token = $this->checkFBaccestoken($facebook);

                if ($facebook->getAccessToken() != $access_token)
                {
                    // Reset access token in session and renew authentication.
                    $this->getHybridauth()->storage()->set("hauth_session.facebook.token.access_token", $access_token);
                    $adapter = $this->getHybridauth()->authenticate('Facebook');
                }
                break;
        }
    }

    /**
     * Update access token.
     */
    public function updateAccesstoken($access_token, $adapter, $provider, $userprofile)
    {
        if ($provider == 'openid')
            return;

        $repository = $this->getEm()->getRepository('TH\ZfUser\Entity\UserAccess');

        $useraccess = $repository->findOneBy(
                array
                    (
                    'auth_id' => $userprofile->identifier,
                    'provider' => $provider,
                    'auth_secret' => $adapter->config['keys']['secret']
                )
        );


        $useraccess->setAccessToken($access_token);
        $this->getEm()->persist($useraccess);
        $this->getEm()->flush();
    }

    public function checkFBaccestoken($facebook)
    {
        $getparams = $this->serviceLocator->get('Request')->getQuery();

        // get facebook config data
        $app_id = $facebook->getAppId();
        $app_secret = $facebook->getAppSecret();

        $router = $this->getServiceLocator()->get('Router');
        $redirect_uri = $router->assemble(
                array('action' => 'fbloggedin'), array(
            'name' => 'provider',
            'force_canonical' => true,
                )
        );

        // known valid access token stored in session
        $access_token = $facebook->getAccessToken();

        $code = $getparams->code;

        // If we get a code, it means that we have re-authed the user
        // and can get a valid access_token.
        if (isset($code))
        {
            $token_url = "https://graph.facebook.com/oauth/access_token?client_id="
                    . $app_id . "&redirect_uri=" . urlencode($redirect_uri)
                    . "&client_secret=" . $app_secret
                    . "&code=" . $code . "&display=popup"; // @TODO Why is there a redirecturl here? Why not use the api on $facebook?

            $response = file_get_contents($token_url);
            $params = null;
            parse_str($response, $params);
            $access_token = $params['access_token'];
        }

        // Attempt to query the graph:
        $graph_url = "https://graph.facebook.com/me?" . "access_token=" . $access_token;

        $response = $this->curl_get_file_contents($graph_url);
        $decoded_response = json_decode($response);

        //Check for errors
        if (isset($decoded_response->error) && $decoded_response->error)
        {
            // check to see if this is an oAuth error:
            if ($decoded_response->error->type == "OAuthException")
            {
                // Retrieving a new valid access token.
                $dialog_url = "https://www.facebook.com/dialog/oauth?"
                        . "client_id=" . $app_id
                        . "&redirect_uri=" . urlencode($redirect_uri);
                echo("<script> top.location.href='" . $dialog_url
                . "'</script>");
            } else
            {
                // another error
            }
        } else
        {
            // get user_id, 0 if not logged in
            $user = $facebook->getUser();

            // check if user is logged in
            if ($user)
            {
                // get long-lived access token
                $facebook->setExtendedAccessToken();
                $long_access_token = $facebook->getAccessToken();
            }
            return $long_access_token;
        }
    }

    // note this wrapper function exists in order to circumvent PHP’s
    //strict obeying of HTTP error codes.  In this case, Facebook
    //returns error code 400 which PHP obeys and wipes out
    //the response.
    protected function curl_get_file_contents($URL)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        $err = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);
        if ($contents)
            return $contents;
        else
            return FALSE;
    }

}