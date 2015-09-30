<?php
namespace validations\CustomException;

use Exception;
use Illuminate\Support\MessageBag;

class FormValidationException extends Exception{
	protected $_errors;

	public function __construct($message = "", MessageBag $errors, $code=0, Exception $previous=null){
		$this->_errors = $errors;
		parent::__construct($message, $code, $previous);
	}

	public function getErrors() {
		return $this->_errors;
	}
}