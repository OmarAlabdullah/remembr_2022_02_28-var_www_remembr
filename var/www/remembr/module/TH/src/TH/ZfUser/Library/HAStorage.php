<?php

namespace TH\ZfUser\Library;

class HAStorage extends \Hybrid_Storage
{

    public function config($key, $value = NULL)
    {
        return parent::config($key, $value);
    }

    public function set($key, $value)
    {
        $key = strtolower($key);

        if ($key != 'hauth_session.error.previous')
        {
            $_SESSION["HA::STORE"][$key] = serialize($value);
        }
    }

    /*
     * After a normal login, in which there is no provider connected yet to this account,
     * HA::STORE can be set already
     * I think because after login, the hybridauth session is restored.
     * The HA::STORES session does exist it that case, but is empty.
     * So i added a check to prevent that error.
     */
    public function deleteMatch($key)
    {
        $key = strtolower($key);

        if (!empty($_SESSION["HA::STORE"]))
        {
            $f = $_SESSION['HA::STORE'];
            foreach ($f as $k => $v)
            {
                if (strstr($k, $key))
                {
                    unset($f[$k]);
                }
            }
            $_SESSION["HA::STORE"] = $f;
        }
    }

}