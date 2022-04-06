<?php

abstract class BaseTask
{
	protected $config = array();
	protected $PDO = null;
	protected $logstmt = null;
	protected $curl_errors = array();
	protected $timers = array();

	public function __construct($config)
	{
		if (isset($config) && is_array($config))
		{
			$this->config=$config;
		}
	}

	protected function init()
	{
		if (isset($this->config['db']))
		{
			$this->PDO = new PDO('mysql:host=localhost;dbname='.$this->config['db']['name'], $this->config['db']['user'], $this->config['db']['pass']);
			$this->logstmt = $this->PDO->prepare(isset($this->config['db']['logstmt']) ? $this->config['db']['logstmt'] : 'INSERT INTO LogEntry(time, message, extra, priority, priorityName) VALUES (NOW(),?,?,6,\'INFO\')');
		}

		$constants = get_defined_constants(true);
		$this->curl_errors = preg_grep('/^CURLE_/', array_flip($constants['curl']));
	}

    public function run()
	{
		$this->init();
		$this->task();
    }

	protected abstract function task();

	/**
	 *	Log a message to database or file (if there is no prepared PDO-statement)
	 */
	protected function log($message, $extra = '')
	{
		if (is_null($this->logstmt))
		{
			error_log(date('Y-m-d H:i:s') .PHP_EOL.print_r($message,1) . PHP_EOL.(empty($extra) ? '' : print_r($extra,1) . PHP_EOL),
					  3,
					  SCRIPT_PATH . '/../data/log_'.get_class($this));
		}
		else
		{
			$this->logstmt->execute(array(print_r($message,1), print_r($extra,1)));
		}

		if (defined('OPT_DEBUG') && OPT_DEBUG)
		{
			echo print_r($message,1) . PHP_EOL.(empty($extra) ? '' : print_r($extra,1) . PHP_EOL);
		}
	}

	/**
	 *	Sets a lock with the pid of the process, so it can check whether
	 *	the script has been called before and is still active.
	 */
	protected function runonce()
	{
		$curpid = getmypid();
		$lockfile = SCRIPT_PATH . '/../data/.lock_'.get_class($this);

		if (file_exists($lockfile))
		{
			$oldpid = file_get_contents($lockfile);
			$res = shell_exec('kill -0 '.$oldpid.' 2>&1 '); // test if pid is active.
			if (!$res)
			{
				// previous process still exists
				$this->logger->log("CRON: previous update still running -- exiting", LOG_INFO);
				echo "CRON: previous update still running -- exiting \n";
				exit();
			}
		}
		file_put_contents($lockfile, $curpid);
	}

	/**
	 *	repeatedly calls curl_multi_exec while necessary.
	 */
	protected function multiperform($mh, &$active)
	{
		do
		{
			$mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		return $mrc;
	}

	/**
	 *	read messages for handles; process handles that report they are done.
	 *	returns number of completed requests
	 */
	protected function multiread($mh)
	{
		$k = 0;
		while($info = curl_multi_info_read($mh))
		{
			$handle = $info['handle'];

			if ($info['msg'] != CURLMSG_DONE)
			{
				continue;
			}

			if ($info['result'] == CURLE_OK)
			{
				$this->timers['multicurl'] += microtime(1);
				$this->processResults($handle);
				$this->timers['multicurl'] -= microtime(1);
			}
			else
			{
				$url = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
				$error = $this->curlerrors[$info['result']];
				$this->log("Curl error -- $error $url");
			}

			curl_multi_remove_handle($mh, $handle);
			curl_close($handle);
			$k ++;
		}
		return $k;
	}

	/**
	 *	use multi-curl to run a number of request simultaneously.
	 *	Requests are taken from 'nextCurlHandle', and sent to processResults when done
	 *	@param int $simultaneous the number of curl-handles that will be run simultaneously
	 */
	protected function multicurl($simultaneous=10)
	{
		$this->timers['multicurl'] = -microtime(1);

		$mh = curl_multi_init();

		while (true)
		{
			$k=0;
			while ($k < $simultaneous && $nexthandle = $this->nextCurlHandle())
			{
				curl_multi_add_handle($mh,$nexthandle);
				$k++;
			}
			if ($k == 0) // apparently no next handle, so stop.
			{
				break;
			}

			$active = null;
			$mrc = $this->multiperform($mh, $active);

			while ($active && $mrc == CURLM_OK)
			{
				if (curl_multi_select($mh) != -1)
				{
					$k = $this->multiread($mh);

					// resupply handles.
					while ($k > 0 && $nexthandle = $this->nextCurlHandle())
					{
						$k--;
						curl_multi_add_handle($mh,$nexthandle);
					}

					$mrc = $this->multiperform($mh, $active);
				}
			}
			$this->multiread($mh);
		}

		//close the handles
		curl_multi_close($mh);
		$this->timers['multicurl']	+= microtime(1);
	}


	/**
	 *	Overload this function to process the result from the curl handle
	 *	the handle will be closed by the multicurl function afterwards
	 */
	protected function processResults($handle)
	{
	//	$result = curl_multi_getcontent($handle);
	}


	/**
	 * Overload this function so it provides a stream of curl-handles for multi-curl to use.
	 * One option is to initialize a queue and always have this function return the next element
	 */
	protected function nextCurlHandle()
	{
		return false;
	}

	/**
	 * retrieve url using curl, with optional extra options.
	 */
	protected function curl($url, array $options=array())
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if ($options)
		{
			curl_setopt_array($ch, $options);
		}
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}

}
?>