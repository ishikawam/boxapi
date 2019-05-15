<?php

namespace Maengkom\Box;

class BoxapiException extends \Exception
{
	/**
	 * Override default constructor to add the ability to set $errors
	 *
	 * @param string $message
	 * @param int $code
	 * @param Exception|null $previous
	 * @param [{string, string}] $errors List of errors returned in an HTTP
	 */
	public function __construct($message, $code = 0, Exception $previous = null, $errors = array())
	{
		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			parent::__construct($message, $code, $previous);
		} else {
			parent::__construct($message, $code);
		}
	}
}
