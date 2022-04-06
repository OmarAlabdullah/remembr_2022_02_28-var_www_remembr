<?php

namespace Base\Util;

class Generator
{
	public static function generateKey($length, $lower_only=false) {
        $chars = implode('', range('a', 'z')).implode('', range('0', '9'));
        if (!$lower_only)
            $chars .= implode('', range('A', 'Z'));
		$result = '';
		$max = strlen($chars)-1;
		for ($i = 0; $i < $length; $i++) {
			$result.= $chars[mt_rand(0, $max)];
		}
		return $result;
	}
}

?>
