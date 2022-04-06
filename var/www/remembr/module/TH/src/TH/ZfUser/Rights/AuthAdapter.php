<?php

namespace TH\ZfUser\Rights;

use \Zend\Authentication\Adapter\AdapterInterface;
use \Zend\Authentication\Result;

abstract class AuthAdapter implements AdapterInterface
{
	protected $entityManager;
	protected $log;

	protected $userClass;
	protected $email;
	protected $password;

	public function __construct($userClass, $email, $password, $entityManager, $log)
	{
		$this->userClass = $userClass;
		$this->email = $email;
		$this->password = $password;

		$this->entityManager = $entityManager;
		$this->log = $log;
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function setPassword($password)
	{
		$this->password = $password;
	}

	public function authenticate()
	{
		// Initialize return values
		$code = Result::FAILURE;
		$identity = null;
		$messages = array();

		// Try to fetch the user from the database using the model
		$user = $this->entityManager->getRepository($this->userClass)->findOneBy(array(
			'email' => $this->email,
		));

		// is the user valid?
		if (!$user) {
			$code = Result::FAILURE_IDENTITY_NOT_FOUND;
		} else if (!self::validate_password($this->password, $user->getPassword())) {
			$code = Result::FAILURE_CREDENTIAL_INVALID;
		}
		else
		{
			if ($this->additionalCheck($user, $messages)) {
				$this->postLogin($user);
				$code = Result::SUCCESS;
				$identity = array('email' => $user->getEmail(), 'userId' => $user->getId());
			} else {
				$code = Result::FAILURE;
			}
		}

		if ($code != Result::SUCCESS)
		{
			$messages[] = 'Authentication error';
		}

		return new Result($code, $identity, $messages);
	}

	protected function postLogin($user) {}
	protected function additionalCheck($user, &$messages) { return true; }

	public static function hash($password)
	{
		return sha1($password);
	}

	/*
	 * Password hashing with PBKDF2.
	 * Author: havoc AT defuse.ca
	 * www: https://defuse.ca/php-pbkdf2.htm
	 */

	private static function defineConstants()
	{
		// These constants may be changed without breaking existing hashes.
		define('PBKDF2_HASH_ALGORITHM', 'sha256');
		define('PBKDF2_ITERATIONS', 1000);
		define('PBKDF2_SALT_BYTES', 24);
		define('PBKDF2_HASH_BYTES', 24);

		define('HASH_SECTIONS', 4);
		define('HASH_ALGORITHM_INDEX', 0);
		define('HASH_ITERATION_INDEX', 1);
		define('HASH_SALT_INDEX', 2);
		define('HASH_PBKDF2_INDEX', 3);
	}

	public static function create_hash($password)
	{
		self::defineConstants();

		// format: algorithm:iterations:salt:hash
		$salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTES, MCRYPT_DEV_URANDOM));
		return PBKDF2_HASH_ALGORITHM . ':' . PBKDF2_ITERATIONS . ':' .  $salt . ':' .
			base64_encode(self::pbkdf2(
				PBKDF2_HASH_ALGORITHM,
				$password,
				$salt,
				PBKDF2_ITERATIONS,
				PBKDF2_HASH_BYTES,
				true
			));
	}

	public static function validate_password($password, $good_hash)
	{
		self::defineConstants();

		$params = explode(':', $good_hash);
		if(count($params) < HASH_SECTIONS)
		   return false;
		$pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);
		return self::slow_equals(
			$pbkdf2,
			self::pbkdf2(
				$params[HASH_ALGORITHM_INDEX],
				$password,
				$params[HASH_SALT_INDEX],
				(int)$params[HASH_ITERATION_INDEX],
				strlen($pbkdf2),
				true
			)
		);
	}

	// Compares two strings $a and $b in length-constant time.
	private static function slow_equals($a, $b)
	{
		$diff = strlen($a) ^ strlen($b);
		for($i = 0; $i < strlen($a) && $i < strlen($b); $i++)
		{
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}
		return $diff === 0;
	}

	/*
	 * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
	 * $algorithm - The hash algorithm to use. Recommended: SHA256
	 * $password - The password.
	 * $salt - A salt that is unique to the password.
	 * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
	 * $key_length - The length of the derived key in bytes.
	 * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
	 * Returns: A $key_length-byte key derived from the password and salt.
	 *
	 * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
	 *
	 * This implementation of PBKDF2 was originally created by https://defuse.ca
	 * With improvements by http://www.variations-of-shadow.com
	 */
	private static function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
	{
		$algorithm = strtolower($algorithm);
		if(!in_array($algorithm, hash_algos(), true))
			die('PBKDF2 ERROR: Invalid hash algorithm.');
		if($count <= 0 || $key_length <= 0)
			die('PBKDF2 ERROR: Invalid parameters.');

		$hash_length = strlen(hash($algorithm, '', true));
		$block_count = ceil($key_length / $hash_length);

		$output = "";
		for($i = 1; $i <= $block_count; $i++) {
			// $i encoded as 4 bytes, big endian.
			$last = $salt . pack('N', $i);
			// first iteration
			$last = $xorsum = hash_hmac($algorithm, $last, $password, true);
			// perform the other $count - 1 iterations
			for ($j = 1; $j < $count; $j++) {
				$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
			}
			$output .= $xorsum;
		}

		if($raw_output)
			return substr($output, 0, $key_length);
		else
			return bin2hex(substr($output, 0, $key_length));
	}

}
?>
