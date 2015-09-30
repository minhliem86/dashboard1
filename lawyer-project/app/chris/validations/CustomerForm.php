<?php
namespace validations\Baseform;

class CustomerForm extends Validator{
	public function rules(){
		return [
			'captcha'=>'required|captcha',
			'fullname'=> 'required|max:200',
			'email'=> 'email|required',
			'phone'=>'required',
		];
	} 
}