<?php

define('SCRIPT_PATH', __DIR__);

if(count($argv)>=2)
{
	for ($i=2; $i < count($argv) ; $i++)
	{
		if (preg_match('#-([[:alpha:]][[:alnum:]]*)(?:=(.*))?#', $argv[$i], $match))
		{
			define('OPT_'.strtoupper($match[1]), isset($match[2]) ? $match[2] : true);
		}
	}

	$task = implode('', array_map("ucfirst", explode('-', strtolower($argv[1]))));
	require __DIR__ .'/common/BaseTask.php';
	require __DIR__ .'/tasks/'.$task.'.php';
	$config = require __DIR__.'/config/config.php';

	$test = new $task($config);
	$test->run();
}
else
{
	echo "Task not specified\n";
}
?>