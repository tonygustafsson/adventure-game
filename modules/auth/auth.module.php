<?php
	class auth_module
	{
		public function __construct()
		{
			$this->opus =& opus::$instance;

			if (! session_id()) { session_start(); }

			$this->module_path = 'auth';

			$this->db_id_column = "id";
			$this->db_username_column = "username";
			$this->db_password_column = "password";
			$this->db_verify_password_column = "verify_password";
			$this->db_real_name_column = "real_name";
			$this->db_token_reset_password_column = "token_reset_password";
			$this->db_token_activation_column = "token_activation";
			$this->db_activated_column = "activated";

			$this->session_id_field = 'user_session_id';
			$this->session_ip_field = 'ip';
			$this->session_username_field = 'username';
			$this->session_prev_url_field = 'previous_url';
			$this->remember_session_field = 'remember_session';

			$this->model = $this->opus->load->model('auth');

			//Set a unique ID that don't changes ever 5 minutes as session_id does.
			if (! isset($_SESSION[$this->session_id_field]))
				$_SESSION[$this->session_id_field] = uniqid();

			if (isset($_POST[$this->db_password_column]) && isset($_POST[$this->db_verify_password_column]))
				$this->model->data_model['fields'][$this->db_verify_password_column]['exact_match'] = array($_POST[$this->db_password_column]);

			if (isset($_SESSION[$this->session_username_field]))
			{
				$get_parameter[$this->db_username_column] = $_SESSION[$this->session_username_field];
				$this->user = $this->get_user($get_parameter);
			}
			else
				$this->user = NULL;

			if (isset($_SESSION[$this->session_ip_field]) && $_SESSION[$this->session_ip_field] !== $_SERVER['REMOTE_ADDR'])
				$this->logout(); //If the user has changed IP, log out the user

			$is_restricted = $this->is_restricted($this->opus->url['area'], $this->opus->url['method']);
			$this->opus->prevent_controller_load = $is_restricted;

			//Specify which methods can be loaded through a URL
			$this->url_accessible = array('login', 'login_post', 'register', 'register_post', 'activation',
										  'forgot_password', 'forgot_password_post', 'reset_password',
										  'reset_password_post', 'logout');

			//Ignore methods that does not permitted
			if ($this->opus->url['area'] == $this->module_path && ! in_array($this->opus->url['area'], $this->url_accessible))
				return;

			//Show login view if the page is registricted and the user is not logged in
			if ($is_restricted && $this->user['logged_in'] !== TRUE)
			{
				//If it's not a part of the auth module, remember the URL for redirection
				if ($this->opus->url['area'] != $this->module_path)
					$_SESSION[$this->session_prev_url_field] = $this->opus->url['relative'];

				$this->login();
			}
		}

		public function login()
		{
			$make_settings['wanted_fields'] = array($this->db_username_column, $this->db_password_column, $this->remember_session_field);
			$login_data['form_elements'] = $this->opus->form->make($this->model->data_model, $make_settings);

			$data['page_title'] = "Login";
			$data['css'] = $this->opus->load->css(array('base', 'custom'));
			$data['partial'] = $this->opus->load->view('login', $login_data, TRUE);
			$this->opus->load->view('template', $data);
		}

		public function login_post()
		{
			$get_parameter[$this->db_username_column] = $_POST[$this->db_username_column];
			$user = $this->get_user($get_parameter, $by_pass_session_controll = TRUE);

			if (! isset($user) || ! password_verify($_POST[$this->db_password_column], $user[$this->db_password_column]))
			{
				$this->opus->session->set_flash('error', 'Wrong username or password!');
				$this->opus->load->url($this->module_path . '/login');
			}

			if ($this->opus->config->auth->require_user_activation && (! isset($user) || $user[$this->db_activated_column] != 1))
			{
				$this->opus->session->set_flash('error', 'You need to activate your user before you can login. Please check your mail box.');
				$this->opus->load->url($this->module_path . '/login');
			}

			$this->opus->log->write('info', $user[$this->db_username_column] . ' logged in from ' . $_SERVER['REMOTE_ADDR']);

			//The user ID field says that the user is logged in
			$_SESSION[$this->session_username_field] = $user[$this->db_username_column];
			$_SESSION[$this->session_ip_field] = $_SERVER['REMOTE_ADDR'];

			if ($_POST[$this->remember_session_field] == "1")
				$_SESSION[$this->remember_session_field] = TRUE;

			//Remember the URL the person came from, and redirect him here instead of the startpage
			$url = (isset($_SESSION[$this->session_prev_url_field])) ? $_SESSION[$this->session_prev_url_field] : '/';
			unset($_SESSION[$this->session_prev_url_field]);

			$this->opus->load->url($url);
		}

		public function register()
		{
			$make_settings['wanted_fields'] =  array($this->db_real_name_column, $this->db_username_column, $this->db_password_column, $this->db_verify_password_column);
			$make_settings['validation_errors'] = $this->opus->session->get_flash('form_validation');
			$make_settings['values'] = $this->opus->session->get_flash('form_values');

			$register_data['form_elements'] = $this->opus->form->make($this->model->data_model, $make_settings);

			$data['page_title'] = "Register";
			$data['css'] = $this->opus->load->css(array('base', 'custom'));
			$data['partial'] = $this->opus->load->view('register', $register_data, TRUE);
			$this->opus->load->view('template', $data);
		}

		public function register_post()
		{
			$_POST[$this->db_id_column] = $_SESSION[$this->session_id_field];
			$_POST[$this->db_password_column] = password_hash($_POST[$this->db_password_column], PASSWORD_DEFAULT);
			$_POST[$this->db_activated_column] = 0;

			if ($this->opus->config->auth->require_user_activation)
			{
				$_POST[$this->db_token_activation_column] = uniqid();
				$activation_link = $this->opus->url($this->module_path . '/activation/token=' . $_POST[$this->db_token_activation_column]);
			}
			else
				$_POST[$this->db_token_activation_column] = "";

			
			if ($this->is_registered($_POST[$this->db_username_column]))
				$form_validation = array($this->db_username_column => array(0 => 'This username is already taken.'));
			else
			{
				$insert_settings['data_model'] = $this->model->data_model;
				$insert_settings['fields'] = array($this->db_id_column, $this->db_real_name_column, $this->db_username_column, $this->db_password_column, $this->db_activated_column, $this->db_token_activation_column);
				$insert_output = $this->opus->database->insert($insert_settings);
			}

			//Merge duplicate user check error with the form validation if there is any.
			if (isset($insert_output->form_errors) && isset($form_validation))
				$insert_output->form_errors = array_merge($insert_output->form_errors, $form_validation);
			elseif (! isset($insert_output->form_errors) && isset($form_validation))
				$insert_output->form_errors = $form_validation;

			$form_validation = (isset($insert_output->form_errors) && isset($form_validation)) ? array_merge($insert_output->form_errors, $form_validation) : $insert_output->form_errors;

			if (! isset($insert_output->form_errors))
			{
				if (! $this->opus->config->auth->require_user_activation)
				{
					//Don't log them in if we require activation first
					$_SESSION[$this->session_ip_field] = $_SERVER['REMOTE_ADDR'];
					$_SESSION[$this->session_username_field] = $_POST[$this->db_username_column];
				}
				else
				{
					//Send mail
					$this->opus->email = $this->opus->load->module('email');
					$mail_args['to_name'] = $_POST[$this->db_real_name_column];
					$mail_args['to_email'] = $_POST[$this->db_username_column];
					$mail_args['subject'] = "Registration at " . $this->opus->config->site_name;
					$mail_args['body'] = "Welcome to " . $this->opus->config->site_name . ".\n\n";
					$mail_args['body'] .= "Please activate your user by clicking this link: " . $activation_link . "\r\n";
					
					if ($this->opus->email->send($mail_args))
						$this->opus->session->set_flash('success', 'Welcome! Please check your inbox (' . $_POST[$this->db_username_column] . ') to activate your user!');
					else
						$this->opus->session->set_flash('error', 'Something went wrong when trying to email you.');
				}

				$this->opus->log->write('info', $_POST[$this->db_username_column] . ' registered from ' . $_SERVER['REMOTE_ADDR']);
				$this->opus->load->url('/');
			}
			else
			{
				$this->opus->session->set_flash('form_validation', $insert_output->form_errors);
				$this->opus->session->set_flash('form_values', $_POST);

				$this->opus->load->url($this->module_path . '/register');
			}
		}

		public function activation()
		{
			if (! $this->opus->config->auth->require_user_activation)
			{
				$this->opus->session->set_flash('error', 'Activation is not needed.');
				$this->opus->load->url('/');
			}

			$get_parameters[$this->db_token_activation_column] = $this->opus->urlargs->get_parameter('token');
			$user = $this->get_user($get_parameters, $by_pass_session_controll = TRUE);

			if (! isset($user))
			{
				$this->opus->session->set_flash('error', 'Something is not right with your activation link.');
				$this->opus->load->url('/');
			}

			$_POST[$this->db_activated_column] = 1;
			$_POST[$this->db_token_activation_column] = "";
			
			$update_settings['data_model'] = $this->model->data_model;
			$update_settings['fields'] = array($this->db_activated_column, $this->db_token_activation_column);
			$update_settings['where'][$this->db_token_activation_column] = $user[$this->db_token_activation_column];
			$update_output = $this->opus->database->update($update_settings);

			$this->opus->log->write('info', $user[$this->db_username_column] . ' activated the account.');

			$this->opus->session->set_flash('success', 'You have successfully activated your user!');
			$this->opus->load->url($this->module_path . '/login/');
		}

		public function forgot_password()
		{
			$make_settings['wanted_fields'] =  array($this->db_username_column);
			$make_settings['validation_errors'] = $this->opus->session->get_flash('form_validation');
			$make_settings['values'] = $this->opus->session->get_flash('form_values');

			$forgot_password_data['form_elements'] = $this->opus->form->make($this->model->data_model, $make_settings);

			$data['page_title'] = "Forgot password";
			$data['css'] = $this->opus->load->css(array('base', 'custom'));
			$data['partial'] = $this->opus->load->view('forgot_password', $forgot_password_data, TRUE);
			$this->opus->load->view('template', $data);
		}

		public function forgot_password_post()
		{
			if (! $this->is_registered($_POST[$this->db_username_column]))
			{
				//User already exists
				$form_errors = array($this->db_username_column => array(0 => 'This user is not registered.'));

				$this->opus->session->set_flash('form_validation', $form_errors);
				$this->opus->session->set_flash('form_values', $_POST);

				$this->opus->load->url($this->module_path . '/forgot_password');
			}

			//Get the user
			$get_parameter[$this->db_username_column] = $_POST[$this->db_username_column];
			$user = $this->get_user($get_parameter, $by_pass_session_controll = TRUE);

			//Get a reset token
			$reset_password_token = uniqid();
			$reset_password_url = $this->opus->url($this->module_path . '/reset_password/token=' . $reset_password_token);
			$_POST[$this->db_token_reset_password_column] = $reset_password_token;

			//Write token to database
			$update_settings['data_model'] = $this->model->data_model;
			$update_settings['fields'] = array($this->db_token_reset_password_column);
			$update_settings['where'][$this->db_id_column] = $user[$this->db_id_column];
			$update_output = $this->opus->database->update($update_settings);

			$this->opus->log->write('info', $_POST[$this->db_username_column] . ' wants to reset the password.');

			//Send mail
			$this->opus->email = $this->opus->load->module('email');
			$mail_args['to_name'] = $user[$this->db_real_name_column];
			$mail_args['to_email'] = $user[$this->db_username_column];
			$mail_args['subject'] = "Forgotten password";
			$mail_args['body'] = "You can reset your password by visiting this link: " . $reset_password_url . "\r\n";
			
			if ($this->opus->email->send($mail_args))
				$this->opus->session->set_flash('success', 'A password reset mail has been sent to ' . $_POST[$this->db_username_column] . '.');
			else
				$this->opus->session->set_flash('error', 'Something went wrong when trying to email you.');

			$this->opus->load->url($this->module_path . '/login');
		}

		public function reset_password()
		{
			$get_parameter[$this->db_token_reset_password_column] = $this->opus->urlargs->get_parameter('token');
			$user = $this->get_user($get_parameter, $by_pass_session_controll = TRUE);

			if (isset($user))
			{
				$make_settings['wanted_fields'] =  array($this->db_token_reset_password_column, $this->db_password_column, $this->db_verify_password_column);
				$make_settings['validation_errors'] = $this->opus->session->get_flash('form_validation');
				$make_settings['values'][$this->db_token_reset_password_column] = $this->opus->urlargs->get_parameter('token');

				$reset_password_data['form_elements'] = $this->opus->form->make($this->model->data_model, $make_settings);

				$data['page_title'] = "Reset password";
				$data['css'] = $this->opus->load->css(array('base', 'custom'));
				$data['partial'] = $this->opus->load->view('reset_password', $reset_password_data, TRUE);
				$this->opus->load->view('template', $data);
			}
			else
			{
				$this->opus->session->set_flash('error', 'Something is not right with your password reset link.');
				$this->opus->load->url($this->module_path . '/forgot_password/');
			}
		}

		public function reset_password_post()
		{
			$get_parameters[$this->db_token_reset_password_column] = $_POST[$this->db_token_reset_password_column];
			$user = $this->get_user($get_parameters, $by_pass_session_controll = TRUE);

			if (! isset($user))
			{
				$this->opus->session->set_flash('error', 'Something is not right with your password reset link.');
				$this->opus->load->url($this->module_path . '/forgot_password/');
			}

			$_POST[$this->db_password_column] = password_hash($_POST[$this->db_password_column], PASSWORD_DEFAULT);
			$_POST[$this->db_token_reset_password_column] = "";
			
			$update_settings['data_model'] = $this->model->data_model;
			$update_settings['fields'] = array($this->db_password_column, $this->db_token_reset_password_column);
			$update_settings['where'][$this->db_token_reset_password_column] = $user[$this->db_token_reset_password_column];
			$update_output = $this->opus->database->update($update_settings);

			if (isset($update_output->form_errors))
			{
				$this->opus->session->set_flash('form_validation', $update_output->form_errors);
				$this->opus->session->set_flash('form_values', $_POST);

				$this->opus->load->url($this->module_path . '/reset_password/' . $user[$this->db_token_reset_password_column]);
			}

			$this->opus->log->write('info', $user[$this->db_username_column] . ' resetted the password.');

			$this->opus->session->set_flash('success', 'You have successfully changed password.');
			$this->opus->load->url($this->module_path . '/login/');
		}

		public function logout()
		{
			session_destroy();
			$this->opus->load->url('/');
		}

		public function get_user($get_parameter, $by_pass_session_controll = FALSE)
		{
			$get_settings['data_model'] = $this->model->data_model;
			$get_settings['select'] = array($this->db_id_column, $this->db_username_column, $this->db_real_name_column, $this->db_password_column, $this->db_token_reset_password_column, $this->db_token_activation_column, $this->db_activated_column);

			if (array_key_exists($this->db_username_column, $get_parameter))
				$get_settings['where'][$this->db_username_column] = $get_parameter[$this->db_username_column];
			else if (array_key_exists($this->db_token_reset_password_column, $get_parameter))
				$get_settings['where'][$this->db_token_reset_password_column] = $get_parameter[$this->db_token_reset_password_column];
			else if (array_key_exists($this->db_token_activation_column, $get_parameter))
				$get_settings['where'][$this->db_token_activation_column] = $get_parameter[$this->db_token_activation_column];
			
			$db_user = $this->opus->database->get_row($get_settings);

			//If user does not exists in database
			if (! isset($db_user))
				return NULL;

			//If username does not match the session username (set by login_post)
			if ($by_pass_session_controll !== TRUE && (! isset($_SESSION[$this->session_username_field]) || $_SESSION[$this->session_username_field] != $db_user['username']))
				return NULL;

			$db_user['logged_in'] = TRUE;

			return $db_user;
		}

		public function get_first_name()
		{
			$names = explode(" ", $this->user[$this->db_real_name_column]);
			return $names[0];
		}

		public function is_restricted($controller, $method_name)
		{
			$restricted_array_key = $controller . '/' . $method_name;

			if ($this->opus->config->auth->protect_all !== TRUE)
			{
				//Default permit everything
				$is_restricted =  array_key_exists($restricted_array_key, $this->opus->config->auth->restricted) ? $this->opus->config->auth->restricted[$restricted_array_key] : FALSE;
			}
			else
			{
				//Default permit nothing
				$is_restricted = array_key_exists($restricted_array_key, $this->opus->config->auth->restricted) ? $this->opus->config->auth->restricted[$restricted_array_key] : TRUE;
			}

			if ($is_restricted && (! isset($_SESSION[$this->session_ip_field]) || $_SESSION[$this->session_ip_field] != $_SERVER['REMOTE_ADDR']))
				return TRUE;
			else
				return FALSE;
		}

		public function is_registered($username)
		{
			$get_settings['data_model'] = $this->model->data_model;
			$get_settings['select'] = array($this->db_username_column);
			$get_settings['where'][$this->db_username_column] = $username;
			$db_user = $this->opus->database->get_row($get_settings);

			return (count($db_user) > 0) ? TRUE : FALSE;
		}

	}
?>