<?php

namespace TH\ZfUser\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Hybridauth extends AbstractPlugin
{

    protected $hybridauth;

    public function __invoke($pluginManager = null)
    {
        if ($pluginManager !== null)
            $this->setController($pluginManager->getController());
        
        if ($this->hybridauth == null)
        {
            try
            {
                $this->hybridauth = $this->getController()->getServiceLocator()->get('HybridAuth');
            }
            catch (Exception $e)
            {
                $this->hybridError($e);
            }
        }

        return $this->hybridauth;
    }

    protected function hybridError($e)
    {
        $message = "";

        switch ($e->getCode())
        {
            case 0 : $message = "Unspecified error.";
                break;
            case 1 : $message = "Hybriauth configuration error.";
                break;
            case 2 : $message = "Provider not properly configured.";
                break;
            case 3 : $message = "Unknown or disabled provider.";
                break;
            case 4 : $message = "Missing provider application credentials.";
                break;
            case 5 : $message = "Authentication failed. The user has canceled the authentication or the provider refused the connection.";
                break;

            default: $message = "Unspecified error!";
        }

        // load error template
    }

}

