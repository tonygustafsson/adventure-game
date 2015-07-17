<?php
	class email_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;

			$this->required_args = array('to_email', 'to_name', 'subject', 'body', 'from_name', 'from_email', 'subject', 'body');

			$this->smtp_codes['service_ready'] = 220;
			$this->smtp_codes['command_successfull'] = 250;
			$this->smtp_codes['unknown_command'] = 500;
			$this->smtp_codes['data_ok'] = 354;
			$this->smtp_codes['user_ok'] = 334;
			$this->smtp_codes['auth_ok'] = 235;
			$this->smtp_codes['tls_accepted'] = 454;
			$this->smtp_codes['connection_closed'] = 221;

			if ($this->opus->config->email->enable_emailing)
				@$this->mail = fsockopen($this->opus->config->email->smtp_host, $this->opus->config->email->smtp_port, $errno, $errstr, $this->opus->config->email->smtp_timeout);

			if ($this->opus->config->email->enable_emailing && ! $this->mail)
					exit("ERROR: Failed to connect to SMTP server.");
		}

		public function __destruct()
		{
			@fclose($this->mail);
		}

		public function send($args)
		{
			$args['from_name'] = (isset($args['from_name'])) ? $args['from_name'] : $this->from_name;
			$args['from_email'] = (isset($args['from_email'])) ? $args['from_email'] : $this->from_email;
			$args['html_format'] = (isset($args['html_format'])) ? $args['html_format'] : $this->opus->config->email->html_format;

			//Check if required arguments are available
			foreach ($this->required_args as $required_arg)
			{
				if (! array_key_exists($required_arg, $args))
					return FALSE;
			}

			$this->send_command("HELO " . $_SERVER['SERVER_NAME'], $this->smtp_codes['service_ready']);

			if ($this->opus->config->email->smtp_auth === TRUE)
			{
				//Cannot make TLS work :(
				//$this->send_command("STARTTLS", $this->smtp_codes['command_successfull']);
				$this->send_command("AUTH LOGIN", $this->smtp_codes['command_successfull']);

				//Enable TLS encryption
				//stream_socket_enable_crypto($this->mail, TRUE, STREAM_CRYPTO_METHOD_TLS_CLIENT);

				$this->send_command(base64_encode($this->opus->config->email->smtp_user), $this->smtp_codes['user_ok']);
				$this->send_command(base64_encode($this->opus->config->email->smtp_pass), $this->smtp_codes['user_ok']);
			}

			if (isset($args['from_name']) && isset($args['from_email']) && $this->opus->config->email->smtp_auth !== TRUE)
				$this->send_command("MAIL FROM: <" . $args['from_email'] . ">", $this->smtp_codes['command_successfull']);

			if (isset($args['from_name']) && isset($args['from_email']) && $this->opus->config->email->smtp_auth === TRUE)
				$this->send_command("MAIL FROM: <" . $args['from_email'] . ">", $this->smtp_codes['auth_ok']);

			if (isset($args['to_name']) && isset($args['to_email']))
				$this->send_command("RCPT TO: <" . $args['to_email'] . ">", $this->smtp_codes['command_successfull']);

			$this->send_command("DATA", $this->smtp_codes['command_successfull']);

			if (isset($args['subject']))
				$this->send_command("Subject: " . $args['subject'], $this->smtp_codes['data_ok']);

			$this->send_command("X-Mailer: PHP/" . phpversion());
			$this->send_command("Date: " . date("r"));

			if (isset($args['from_name']) && isset($args['from_email']))
				$this->send_command("From: " . $args['from_name'] . " <" . $args['from_email'] . ">");

			if (isset($args['to_name']) && isset($args['to_email']))
				$this->send_command("To: " . $args['to_name'] . " <" . $args['to_email'] . ">");

			if (isset($args['bbc_name']) && isset($args['bbc_email']))
				$this->send_command("Bcc: " . $args['from_name'] . " <" . $args['from_email'] . ">");

			if (isset($args['reply_to_name']) && isset($args['reply_to_email']))
				$this->send_command("Reply-To: " . $args['from_name'] . " <" . $args['from_email'] . ">");

			if (isset($args['html_format']) && $args['html_format'] === TRUE)
			{
				$this->send_command("MIME-Version: 1.0");
				$this->send_command("Content-Type: text/html; charset=" . $this->opus->config->email->charset);
				$this->send_command("Content-Transfer-Encoding: quoted-printable");
			}

			$this->send_command($args['body']);
			$this->send_command("."); //Queue the mail for delivery
			$this->send_command("quit", $this->smtp_codes['command_successfull']);

			return TRUE;
		}

		private function send_command($command, $wanted_response_code = FALSE)
		{
			//Send a command to the SMTP server, and first wait for a certain error code
			$success = FALSE;

			if (! $this->opus->config->email->enable_emailing || $wanted_response_code === FALSE)
			{
				//No need to wait for the right answer before sending.
				if ($this->opus->config->email->enable_emailing)
				{
					fwrite($this->mail, $command . "\r\n");
				}

				$success = TRUE;
			}
			else
			{
				for ($x = 0; $x < $this->opus->config->email->command_retries; $x++)
				{
					usleep($this->opus->config->email->command_retry_timeout);
					$smtp_response = $this->read_response();
					$smtp_code = substr($smtp_response, 0, 3); //Get three first chars, like "250"

					$this->opus->log->write('debug', 'SMTP Respond: ' . trim($smtp_response));

					if (substr($smtp_code, 0, 1) == "5")
						exit('Recieved SMTP error: ' . $smtp_response);

					if ($smtp_code == $wanted_response_code)
					{
						if ($this->opus->config->email->enable_emailing)
						{
							$this->opus->log->write('debug', 'SMTP Send (OK Response: ' . $wanted_response_code . '): ' . $command);
							fwrite($this->mail, $command . "\r\n");
						}

						$success = TRUE;
						break;
					}
				}
			}

			if ($this->opus->config->email->enable_emailing && $success === FALSE)
				exit('Could not send command: "' . $command . '". Aborting email.<p>');
		}

		private function read_response()
		{
			$data = "";

			if (isset($this->mail))
			{
				while ($str = fread($this->mail, 512))
				{
					$data .= $str;

					if (substr($str, 3, 1) == " ")
						break;
				}
			}

			return $data;
		}

	}
?>