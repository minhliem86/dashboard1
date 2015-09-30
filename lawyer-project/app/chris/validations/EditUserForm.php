<?php
namespace validations;

use validations\Baseform\Validator;

class EditUserForm extends Validator{
	public function rules(){
		return array(
			'dienthoai'=>'required',
			'diachi'=>'required',
		);
	}
}