<?php
	class session_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;

			if (! session_id()) { session_start(); }

			//Log out visitors with old sessions
			$this->logout_old_sessions();
			$_SESSION[$this->opus->config->session->created_identifier] = time();

			$this->remove_old_flash();
			$this->prepare_current_flash();
		}

		private function logout_old_sessions()
		{
			//Logout users that has a session older than $this->opus->config->session->timeout minutes
			if
			(
				! isset($_SESSION['remember_session'])
				&& isset($_SESSION[$this->opus->config->session->created_identifier])
				&& (time() - $_SESSION[$this->opus->config->session->created_identifier]) > $this->opus->config->session->timeout
			)
			{
				$this->opus->log->write('info', session_id() . ' logged out due to session timeout (' . (time() - $_SESSION[$this->opus->config->session->created_identifier]) . '/' . $this->opus->config->session->timeout . ' seconds)');

				session_unset();
				session_destroy();

				session_start();
			}
		}

		public function remove_old_flash()
		{
			foreach ($_SESSION as $key => $val)
			{
				$flash_current_identifier = $this->opus->config->session->flash_pre_name . $this->opus->config->session->flash_current_name;

				if (substr($key, 0, strlen($flash_current_identifier)) === $flash_current_identifier)
				{
					unset($_SESSION[$key]);
				}
			}
		}

		public function prepare_current_flash()
		{
			foreach ($_SESSION as $key => $val)
			{
				$flash_current_identifier = $this->opus->config->session->flash_pre_name . $this->opus->config->session->flash_current_name;
				$flash_next_identifier = $this->opus->config->session->flash_pre_name . $this->opus->config->session->flash_next_name;

				if (substr($key, 0, strlen($flash_next_identifier)) === $flash_next_identifier)
				{
					$flash_key = str_replace($flash_next_identifier, "", $key);
					$flash_new_name = $this->opus->config->session->flash_pre_name . $this->opus->config->session->flash_current_name . $flash_key;

					$_SESSION[$flash_new_name] = $val;
					unset($_SESSION[$key]);
				}
			}
		}

		public function set_flash($name, $data)
		{
			$session_name = $this->opus->config->session->flash_pre_name . $this->opus->config->session->flash_next_name . $name;

			$this->opus->log->write('debug', 'Flash set: ' . $session_name);

			$_SESSION[$session_name] = $data;
		}

		public function get_flash($name)
		{
			$session_name = $this->opus->config->session->flash_pre_name . $this->opus->config->session->flash_current_name . $name;

			if (isset($_SESSION[$session_name]) && ! empty($_SESSION[$session_name]))
			{
				$this->opus->log->write('debug', 'Flash get: ' . $name);

				return $_SESSION[$session_name];
			}
			else
			{
				return "";
			}
		}

	}
?>