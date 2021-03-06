<?php
namespace admin\controllers;

use services\User\RepoInterface as User;
use validations\CreateUserForm;
use validations\EditUserForm;
use validations\CustomException\FormValidationException;

class UserController extends \BaseController{
	public $user;
	public $validatorCreate;
	public $validatorEdit;


	public function __construct(User $user, CreateUserForm $validatorCreate, EditUserForm $validatorEdit){
		$this->user = $user;
		$this->validatorCreate = $validatorCreate;
		$this->validatorEdit = $validatorEdit;
	}
	// CREATE
	public function createUser(){
		return \View::make('admin::pages.user.create');
	}

	public function  post_createUser(){
		$data = \Input::all();
		try{
			$this->validator->validate($data);
		}
		catch(FormValidationException $e){
			return \Redirect::back()->withInput()->withErrors($e->getErrors());
		}
		$user = array(
			'username' =>\Input::get('username'),
			'password' =>md5(\Input::get('password')),
			'email' =>\Input::get('email'),
			'hoten' =>\Input::get('hoten'),
			'dienthoai' =>\Input::get('dienthoai'),
			'diachi' =>\Input::get('diachi'),
			'level' =>\Input::get('level'),
			'activated' =>1,
		);
		$this->user->create($user);

		return \Redirect::to('admin')->with('success','Done');
		
	}

	// EDIT
	public function editUser(){
		if(\Auth::check()){
			$user = $this->user->find(\Auth::id())
			return \View::make('user.view')->with('user',$user);
		}else{
			return "Bạn chưa đang nhập";
		}
	}
	public function post_editUser(){
		if(!\Auth::check()){
			return \Redirect::back()->with('error','Bạn chưa đăng nhập');
		}else{
			$data = \Input::all();
			try{
				$this->validatorEdit->validate($data);
			}
			catch(FormValidationException $e){
				return \Redirect::back()->withErrors($e->getErrors())->withInput();
			}
			$user = $this->user->find(\Auth::id())
			$user->dienthoai = \Input::get('dienthoai');
			$user->diachi = \Input::get('diachi');
			$user->save();

			return \Redirect::back()->with('success','Updated !');	
		}
	}

	// LOGIN
	public function login(){
		return \View::make('admin::login');
	}

	public function postlogin(){
		if(\Auth::attempt(array('username'=>\Input::get('username'),'password'=>md5(\Input::get('password')),'level'=>'1'))){
			return \Redirect::route('admin_home')->with('success','Welcome to DashBoard');
		}else{
			if(\User::where('username',\Input::get('username'))->count() == 0){
				return \Redirect::back()->with('error','Invalid Username')->withInput(); 
			}elseif(\User::where('password',md5(\Input::get('password')))){
				return \Redirect::back()->with('error','Invalid Password')->withInput();
			}
		}
	}

	public function logout(){
		\Auth::logout();
		return \Redirect::to('admin')->with('success','See you later !');
	}

	// CHANGE PASSWORD
	public function changePass(){
		return \View::make('admin::pages.user.changePassword');
	}
	public function post_changePass(){
		$data = \Input::all();
		$valid = \Validator::make($data,\User::$rule_changepass,\User::$mess_changepass);
		if($valid->passes()){
			if(\User::where('password',md5(\Input::get('oldpassword')))){
				$user = \User::find(\Auth::id());
				$user->password = md5(\Input::get('password'));
				$user->save();
				return \Redirect::back()->with('success','Successful');
			}else{
				return \Redirect::back()->with('error','Password is not correct');
			}
		}else{
			return Redirect::back()->with('error',$valid->errors()->first());
		}
	}

	public function post_forgetPassword(){
		$valid = \Validator::make(\Input::all(),\User::$rule_forget,\User::$mess_forget);
		if($valid->passes()){
			try{
				$user = \Sentry::findUserByLogin(\Input::get('username'));
				$resetCode = $user->getResetPasswordCode();

				\Mail::send('admin::emails.user.forget',array('name' => $user->username,'resetCode'=>$resetCode,'id'=>$user->id), function($message) use ($user){
					$message->from('hoclaravel1986@gmail.com','Admin HocLaravel');
					$message->to($user->email, $user->last_name)->subject('[Admin HocLaravel] Reset Password');
				});

				return \Redirect::back()->with('success','Mã reset password đã được gửi');
			}
			catch(Cartalyst\Sentry\Users\UserNotFoundException $e){
				return \Redirect::back()->with('error','User không tồn tại');
			}
		}else{
			return \Redirect::back()->with('error',$valid->errors()->first());
		}
	}

	public function checkCodeReset($id,$code){
		try{
			$user = \Sentry::findUserById($id);
			if($user->checkResetPasswordCode($code)){
				$newPass = \Str::random(8);
				if($user->attemptResetPassword($code,$newPass)){
					\Mail::send('admin::emails.user.reset',array('name'=>$user->username,'pass'=>$newPass),function($message) use ($user){
						$message->from('hoclaravel1986@gmail.com','Admin HocLaravel');
						$message->to($user->email, $user->last_name)->subject('[Admin HocLaravel] New Password');
					});
					return \Redirect::to('admin/login')->with('success','Password mới đã được gửi đến email của bạn');
				}else{
					return \Redirect::to('admin/login')->with('error','Quá trình reset Password có lỗi. Hãy thực hiện lại việc reset Password');
				}
			}else{
				return \Redirect::to('admin/login')->with('error','Code reset Password không chính xác');
			}
		}
		catch(Cartalyst\Sentry\Users\UserNotFoundException $e){
			return \Redirect::to('admin/login')->with('error','User này không tồn tại');
		}
	}

	public function getUser($id){
		return \View::make('admin::pages.user.changePassword')->with('title','Thay đổi mật khẩu');
	}

	public function postUser(){
		$data = \Input::all();
		$valid = \Validator::make($data,\User::$rule_changepass,\User::$mess_changepass);
		
		if($valid->passes()){
			if(\Auth::user()->password == md5(\Input::get('oldpass'))){
				$user = \User::find(\Auth::id());
				$user->password = md5(\Input::get('newpass'));
				$user->save();
				return \Redirect::back()->with('success','Successful');
			}else{
				return \Redirect::back()->with('error','Password is not correct');
			}
		}else{
			return \Redirect::back()->with('error',$valid->errors()->first());
		}
	}

}