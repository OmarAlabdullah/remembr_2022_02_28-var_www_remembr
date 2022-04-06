#!/usr/bin/env php
<?php
/**
 * call: php ./bin/po2json.php
*/

require_once 'translate/PoeditParser.php';

function buildOptions($args)
{
    $options = array(
        '-o' => null,
        '-i' => null,
        '-n' => 'l10n'
    );
    $len = count($args);
    $i = 0;
    while ($i < $len)
    {
        if (preg_match('#^-[a-z]$#i', $args[$i]))
        {
            $options[$args[$i]] = isset($args[$i + 1]) ? trim($args[$i + 1]) : true;
            $i += 2;
        }
        else
        {
            $options[] = $args[$i];
            $i++;
        }
    }
    return $options;
}

$appdir = dirname(__DIR__);

// process all po-files
foreach (glob($appdir . "/language/" . "*.po") as $pofile) {
    // I presume that the website language is the same as the first part of the locale nl_NL.
    // If not, so if nl_BE are needed for instance, than we have to change this.
    
    // Distract the language.
    $arr = explode("_", basename($pofile));
    $lang = $arr[0];

    $pargv = array(
        0 => '',
        1 => '-i', // input
        2 => $pofile,
        3 => '-l', // language
        4 => $lang,
        5 => '-o', // output
        6 => $appdir . '/public/language/' . $lang
    );

    $options = buildOptions($pargv);

    if (!file_exists($options['-i']) || !is_readable($options['-i']))
    {
        die("Invalid input file. Make sure it exists and is readable.");
    }

    $poeditParser = new PoeditParser($options['-i']);
    $poeditParser->parse();

	/* filter on js translations */
	$filter = function($s){
		$res = array_filter($s->comments, function($a){	return preg_match('/\.js:\d+$/', $a); } ) ;
		return count($res);
	};

    if ($poeditParser->toJSON($options['-o'], $filter))
    {
        $strings = count($poeditParser->getStrings());
        echo "Successfully exported " . $strings . " strings for: " . $lang . "\n";
    }
    else
    {
        echo "Cannot write to file '{$options['-o']}'.\n";
    }
}
