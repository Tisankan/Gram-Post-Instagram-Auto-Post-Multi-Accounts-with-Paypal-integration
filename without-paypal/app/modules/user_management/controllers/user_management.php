<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class user_management extends MX_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
	}

	public function index(){
		if(IS_ADMIN != 1) redirect(PATH."dashboard");
		$data = array(
			"result" => $this->model->getUserList()
		);
		$this->template->title(l('User management'));
		$this->template->build('index', $data);
	}

	public function update(){
		if(IS_ADMIN != 1) redirect(PATH."dashboard");
		$data = array(
			"result"  => $this->model->get("*", USER_MANAGEMENT, "id = '".get("id")."'")
		);
		$this->template->title(l('User management'));
		$this->template->build('update', $data);
	}

	public function ajax_update(){
		if(IS_ADMIN != 1) redirect(PATH."dashboard");
		$id = (int)post("id");

		if(post("fullname") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Fullname is required')
			));
		}

		if(post("email") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Email is required')
			));
		}

		if(!filter_var(post("email"), FILTER_VALIDATE_EMAIL)){
		  	ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Invalid email format')
			));
		}

		if(post("expiration_date") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Expiration date is required')
			));
		}

		if(post("timezone") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Timezone is required')
			));
		}

		$data = array(
			"fullname"        => post("fullname"),
			"email"           => post("email"),
			"maximum_account" => (int)post("maximum_account"),
			"expiration_date" => date("Y-m-d", strtotime(post("expiration_date"))),
			"timezone"        => post("timezone"),
			"status"          => (int)post("status"),
			"changed"         => NOW
		);

		if($id == 0){
			if(post("password") == ""){
				ms(array(
					"st"    => "error",
					"label" => "bg-red",
					"txt"   => l('Password is required')
				));
			}

			if(strlen(post("password")) < 6){
				ms(array(
					"st"    => "error",
					"label" => "bg-red",
					"txt"   => l('Passwords must be at least 6 characters')
				));
			}

			if(post("password") != post("repassword")){
				ms(array(
					"st"    => "error",
					"label" => "bg-red",
					"txt"   => l('Password incorrect')
				));
			}

			$data["password"] = md5(post("password"));
			$data["type"]     = "direct";
			$data["created"]  = NOW;

			$this->db->insert(USER_MANAGEMENT, $data);
			$id = $this->db->insert_id();
		}else{
			if(post("password") != ""){
				if(strlen(post("password")) < 6){
					ms(array(
						"st"    => "error",
						"label" => "bg-red",
						"txt"   => l('Passwords must be at least 6 characters')
					));
				}

				if(post("password") != post("repassword")){
					ms(array(
						"st"    => "error",
						"label" => "bg-red",
						"txt"   => l('Password incorrect')
					));
				}

				$data["password"] = md5(post("password"));
			}

			$this->db->update(USER_MANAGEMENT, $data, array("id" => $id));
		}

		ms(array(
			"st"    => "success",
			"label" => "bg-light-green",
			"txt"   => l('Update successfully')
		));
	}

	public function ajax_action_item(){
		if(IS_ADMIN != 1) redirect(PATH."dashboard");
		$id = (int)post('id');
		$POST = $this->model->get('*', USER_MANAGEMENT, "id = '{$id}'");
		if(!empty($POST)){
			switch (post("action")) {
				case 'delete':
					$this->db->delete(USER_MANAGEMENT, "id = '{$id}'");
					$this->db->delete(INSTAGRAM_ACCOUNTS, "uid = '{$id}'");
					$this->db->delete(INSTAGRAM_SCHEDULES, "uid = '{$id}'");
					$this->db->delete(SAVE, "uid = '{$id}'");
					$this->db->delete(CATEGORIES, "uid = '{$id}'");
					break;
				
				case 'active':
					$this->db->update(USER_MANAGEMENT, array("status" => 1), "id = '{$id}'");
					break;

				case 'disable':
					$this->db->update(USER_MANAGEMENT, array("status" => 0), "id = '{$id}'");
					break;
			}
		}

		ms(array(
			"st"    => "success",
			"label" => "bg-light-green",
			"txt"   => l('Update successfully')
		));
	}

	public function ajax_action_multiple(){
		if(IS_ADMIN != 1) redirect(PATH."dashboard");
		$ids =$this->input->post('id');
		if(!empty($ids)){
			foreach ($ids as $id) {
				$POST = $this->model->get('*', USER_MANAGEMENT, "id = '{$id}'");
				if(!empty($POST)){
					switch (post("action")) {
						case 'delete':
							$this->db->delete(USER_MANAGEMENT, "id = '{$id}'");
							$this->db->delete(INSTAGRAM_ACCOUNTS, "uid = '{$id}'");
							$this->db->delete(INSTAGRAM_SCHEDULES, "uid = '{$id}'");
							$this->db->delete(SAVE, "uid = '{$id}'");
							$this->db->delete(CATEGORIES, "uid = '{$id}'");
							break;
						case 'active':
							$this->db->update(USER_MANAGEMENT, array("status" => 1), "id = '{$id}'");
							break;

						case 'disable':
							$this->db->update(USER_MANAGEMENT, array("status" => 0), "id = '{$id}'");
							break;
					}
				}
			}
		}

		print_r(json_encode(array(
			'st' 	=> 'success',
			'txt' 	=> l('Update successfully')
		)));
	}

	public function profile(){
		$user = $this->model->get("*", USER_MANAGEMENT, "id = '".session("uid")."'");
		if(empty($user)) redirect(PATH);

		$data = array(
			"result"  => $user
		);
		$this->template->title('Dashboard');
		$this->template->build('profile', $data);
	}

	public function ajax_profile(){
		$id = (int)post("id");

		if(post("fullname") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Fullname is required')
			));
		}
		
		if(post("timezone") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Timezone is required')
			));
		}

		$data = array(
			"fullname"        => post("fullname"),
			"timezone"        => post("timezone"),
			"changed"         => NOW
		);

		if(post("password") != ""){
			if(strlen(post("password")) < 6){
				ms(array(
					"st"    => "error",
					"label" => "bg-red",
					"txt"   => l('Passwords must be at least 6 characters')
				));
			}

			if(post("password") != post("repassword")){
				ms(array(
					"st"    => "error",
					"label" => "bg-red",
					"txt"   => l('Password incorrect')
				));
			}

			$data["password"] = md5(post("password"));
		}

		$this->db->update(USER_MANAGEMENT, $data, array("id" => session("uid")));

		ms(array(
			"st"    => "success",
			"label" => "bg-light-green",
			"txt"   => l('Update successfully')
		));
	}

	public function openid($type = ""){
		$result = array();
		switch ($type) {
			case 'facebook':
				if(get('code') && get('state')){
					$USER = FACEBOOK_GET_USER();
					if(!empty($USER) && isset($USER['id'])){
						$data = array(
							"type"            => "facebook",
							"pid"             => $USER['id'],
							"fullname"        => $USER['name'],
							"status"          => AUTO_ACTIVE_USER,
							'maximum_account' => MAXIMUM_ACCOUNT_DEFAULT,
							"changed"         => NOW
						);

						$data["expiration_date"] = date('Y-m-d', strtotime("+".TRIAL." days"));

						if(isset($USER['email'])){
							$data['email'] = $USER['email'];
						}

						if(isset($data['email'])){
							$result = $this->model->get('*', USER_MANAGEMENT, "email = '".$data['email']."'");
						}else{
							$result = $this->model->get('*', USER_MANAGEMENT, "pid = '".$USER['id']."'");
						}
						if(empty($result)){
							if(REGISTER_ALLOWED == 1){
								$data["created"] = NOW;
								$this->db->insert(USER_MANAGEMENT, $data);
								$id = $this->db->insert_id();
								$result = $this->model->get('*', USER_MANAGEMENT, "id = '".$id."'");
							}
						}else{
							$this->db->update(USER_MANAGEMENT, $data, array('id' => $result->id));
						}
					}
				}
				break;
			case 'google':
				if(get('code')){
					$USER = GOOGLE_GET_USER(get('code'));
					if(!empty($USER)){
						$data = array(
							"type"            => "google",
							"pid"             => $USER->id,
							"fullname"        => $USER->name,
							"email"           => $USER->email,
							"status"          => AUTO_ACTIVE_USER,
							'maximum_account' => MAXIMUM_ACCOUNT_DEFAULT,
							"changed"         => NOW
						);

						$data["expiration_date"] = date('Y-m-d', strtotime("+".TRIAL." days"));

						$result = $this->model->get('*', USER_MANAGEMENT, "email = '".$USER->email."'");
						if(empty($result)){
							if(REGISTER_ALLOWED == 1){
								$data["created"] = NOW;
								$this->db->insert(USER_MANAGEMENT, $data);
								$id = $this->db->insert_id();
								$result = $this->model->get('*', USER_MANAGEMENT, "id = '".$id."'");
							}
						}else{
							$this->db->update(USER_MANAGEMENT, $data, array('id' => $result->id));
						}
					}
				}
				break;
			case 'twitter':
				if(get('oauth_token') && get('oauth_verifier')){
					$USER = TWITTER_GET_USER();
					if(!empty($USER)){
						$data = array(
							"type"            => "twitter",
							"pid"             => $USER->id_str,
							"fullname"        => $USER->name,
							"status"          => AUTO_ACTIVE_USER,
							'maximum_account' => MAXIMUM_ACCOUNT_DEFAULT,
							"changed"         => NOW
						);

						$data["expiration_date"] = date('Y-m-d', strtotime("+".TRIAL." days"));

						if(isset($USER->email)){
							$data['email'] = $USER->email;
						}

						if(isset($data['email'])){
							$result = $this->model->get('*', USER_MANAGEMENT, "email = '".$data['email']."'");
						}else{
							$result = $this->model->get('*', USER_MANAGEMENT, "pid = '".$USER->id_str."'");
						}
						if(empty($result)){
							if(REGISTER_ALLOWED == 1){
								$data["created"] = NOW;
								$this->db->insert(USER_MANAGEMENT, $data);
								$id = $this->db->insert_id();
								$result = $this->model->get('*', USER_MANAGEMENT, "id = '".$id."'");
							}
						}else{
							$this->db->update(USER_MANAGEMENT, $data, array('id' => $result->id));
						}
					}
				}
				break;
		}

		if(!empty($result) && $result->status == 1){
			$this->model->session($result);
			redirect(PATH."#login-success");
		}else{
			redirect(PATH."#not-active");
		}
	}

	public function ajax_login(){
		if(post("email") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Email is required')
			));
		}

		if(!filter_var(post("email"), FILTER_VALIDATE_EMAIL)){
		  	ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Invalid email format')
			));
		}

		if(post("password") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Password is required')
			));
		}

		$user = $this->model->get("*", USER_MANAGEMENT, "email = '".post('email')."' AND password = '".md5(post("password"))."'");
		if(!empty($user)){
			if($user->status == 1){
				$this->model->session($user);
				ms(array(
					"st"    => "success",
					"label" => "bg-light-green",
					"txt"   => l('Login successfully')
				));
			}else{
				ms(array(
					"st"    => "error",
					"label" => "bg-red",
					"txt"   => l('Your account has not been activated')
				));
			}
		}else{
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Username or password incorrect')
			));
		}
	}

	public function ajax_register(){
		if(!REGISTER_ALLOWED) redirect(PATH);

		if(post("fullname") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Fullname is required')
			));
		}

		if(post("email") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Email is required')
			));
		}

		if(!filter_var(post("email"), FILTER_VALIDATE_EMAIL)){
		  	ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Invalid email format')
			));
		}

		if(strlen(post('password')) == ''){
    		ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Password is required')
			));
    		exit(0);
    	}

		if(strlen(post("password")) < 6){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Passwords must be at least 6 characters')
			));
		}

		if(post("password") != post("repassword")){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Password incorrect')
			));
		}

		$POST = $this->model->get('*', USER_MANAGEMENT, "email = '".post('email')."'");
		if(empty($POST)){
			$data = array(
				'fullname'=> post("fullname"),
				'type'    => 'direct',
	    		'email'   => post('email'),
	    		'password'=> md5(post('password')),
	    		'maximum_account'  => MAXIMUM_ACCOUNT_DEFAULT,
	    		'status'  => AUTO_ACTIVE_USER,
	    		'changed' => NOW,
	    		'created' => NOW
	    	);

			$data["expiration_date"] = date('Y-m-d', strtotime("+".TRIAL." days"));

    		$this->db->insert(USER_MANAGEMENT, $data);
    		$id = $this->db->insert_id();
    		if(AUTO_ACTIVE_USER == 1){
	    		set_cookie("uid", $id, 1209600);
				set_session("uid", $id);
				if (!file_exists('uploads/user'.(string)$id)) {
		    		mkdir('uploads/user'.$id, 0777, true);
				}
				set_cookie('folderid', 'user'.(string)$id, 86400);
			}
    		ms(array(
				'st' 	=> 'success',
				'label' => "bg-light-green",
				'txt' 	=> l('Register successfully')
			));
    	}else{
    		ms(array(
				'st' 	=> 'error',
				'label' => "bg-red",
				'txt' 	=> l('Email already exists')
			));
    	}
	}

	public function ajax_timezone(){
		if(session("uid")){
			$this->db->update(USER_MANAGEMENT, array("timezone" => post("timezone")), array("id" => (int)session("uid")));
			ms(array(
				"st"    => "success",
				"label" => "bg-light-green",
				"txt"   => l('Update successfully')
			));
		}
	}
}