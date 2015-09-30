<?php
namespace validations;

use validations\Baseform\Validator;

class CreateUserForm extends Validator{

	public function  rules(){
		return array(
			'username'=>array('required','min:4'),
			'email'=>'required|email',
			'hoten'=>'required',
			'password' => 'required',
			'confirmed_password'=>'required|same:password',
			'dienthoai'=>'required|numeric',
			'diachi'=>'required',
		);
	}
		
}