<?php
namespace validations\Baseform;

// use Illuminate\Support\MessageBag;
use Illuminate\Validation\Factory as ValidatorFactory;
// use Illuminate\Validation\Validator;
use validations\CustomException\FormValidationException;

abstract class Validator{

	protected $validator;

	protected $validation;

	public function __construct(ValidatorFactory $factory){
		$this->validator = $factory;
	}

	public function validate(array $input){
		

		$this->validation = $this->validator->make($input, $this->rules() );

		if($this->validation->fails()){
			 throw new FormValidationException('Validation Failed',$this->getValidErrors());
		}

		return true;
	}

	abstract protected  function rules();

	// protected function messages(){

	// }

	protected function getValidErrors(){
		return $this->validation->errors();
	}

}