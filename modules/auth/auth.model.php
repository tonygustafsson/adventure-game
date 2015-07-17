<?php

	class auth_model
	{

		public function __construct()
		{
			//Settings for the data, both on the database side and html form side
			
			$this->data_model = array(
				'db_table' => 'users',
				'fields' => array(
					'id' => array(
						'friendly_name' => 'ID',
						'type' => 'string',
						'form_name' => 'id',
						'max_length' => 30
					),
					'username' => array(
						'friendly_name' => 'Email address',
						'type' => 'string',
						'form_name' => 'username',
						'min_length' => 3,
						'max_length' => 150
					),
					'real_name' => array(
						'friendly_name' => 'Name',
						'type' => 'string',
						'form_name' => 'real_name',
						'min_length' => 3,
						'max_length' => 150
					),
					'password' => array(
						'friendly_name' => 'Password',
						'type' => 'password',
						'form_name' => 'password',
					),
					'verify_password' => array(
						'friendly_name' => 'Verify password',
						'type' => 'password',
						'form_name' => 'verify_password'
					),
					'remember_session' => array(
						'friendly_name' => 'Remember me',
						'type' => 'bool',
						'form_name' => 'remember_session'
					),
					'token_reset_password' => array(
						'friendly_name' => 'Reset password',
						'type' => 'string',
						'form_name' => 'token_reset_password',
						'max_length' => 50,
						'hidden' => TRUE
					),
					'token_activation' => array(
						'friendly_name' => 'Activation token',
						'type' => 'string',
						'form_name' => 'token_activation',
						'max_length' => 50,
						'hidden' => TRUE
					),
					'activated' => array(
						'friendly_name' => 'Activated',
						'type' => 'int',
						'form_name' => 'activated',
						'max_length' => 1,
						'hidden' => TRUE
					)
				)
			);
		}

	}
	
?>