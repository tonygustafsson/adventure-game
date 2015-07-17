<?php
	class form_module {

		public function __construct()
		{
			$this->opus =& opus::$instance;

			$this->url_accessible = array('filter');
		}

		public function make($data_model, $settings)
		{
			$wanted_fields = $settings['wanted_fields'] ?: FALSE;
			$values = (isset($settings['values']) && count($settings['values']) > 0) ? $settings['values'] : FALSE;
			$validation_errors = (isset($settings['validation_errors']) && ! empty($settings['validation_errors'])) ? $settings['validation_errors'] : FALSE;

			$html = "";

			foreach ($wanted_fields as $wanted_field)
			{
				if (array_key_exists($wanted_field, $data_model['fields']))
				{
					$field = $data_model['fields'][$wanted_field];
					$value = "";

					if ($values !== FALSE && isset($values[$wanted_field]))
						$value = $values[$wanted_field];
					else if (isset($field['default_value']))
						$value = $field['default_value'];

					if ($field['type'] == 'id')
					{
						$html .= '
							<input type="hidden" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $value . '">
						';
					}

					if ($field['type'] == 'string')
					{
						if (isset($field['hidden']) && $field['hidden'] === TRUE)
						{
							$html .= '
								<input type="hidden" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $value . '">
							';
						}
						else
						{
							$html .= '
								<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
								<input type="text" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $value . '">
							';
						}
					}

					if ($field['type'] == 'int')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
							<input type="number" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $value . '">
						';
					}

					if ($field['type'] == 'bool')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
							<input type="hidden" name="' . $field['form_name'] . '" value="0">
							<input type="checkbox" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="1"' . ((! empty($value) && $value == 1) ? ' checked' : '') . '>
						';
					}

					if ($field['type'] == 'email')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
								<input type="email" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $value . '">
						';
					}

					if ($field['type'] == 'date')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
							<input type="date" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . ((! empty($value)) ? $this->to_date_format($value) : date('Y-m-d')) . '">
						';
					}

					if ($field['type'] == 'select' && isset($field['fixed_values']))
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
							<select name="' . $field['form_name'] . '" id="' . $field['form_name'] . '">
						';

						foreach ($field['fixed_values'] as $current_key => $current_value)
						{
							if (isset($field['default_value']) && $field['default_value'] == $current_value && empty($value))
							{
								$html .= '<option value="' . $current_key . '" selected>' . $current_value . '</option>';
							}
							else if (! empty($value) && $current_value == $value)
							{
								$html .= '<option value="' . $current_key . '" selected>' . $current_value . '</option>';
							}
							else
							{
								$html .= '<option value="' . $current_key . '">' . $current_value . '</option>';
							}
						}

						$html .= '</select>';
					}

					if ($field['type'] == 'radio' && isset($field['fixed_values']))
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
						';

						foreach ($field['fixed_values'] as $current_key => $current_value)
						{
							if (isset($field['default_value']) && $field['default_value'] == $current_value && empty($value))
							{
								$html .= '<input type="radio" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $current_key . '" checked>' . $current_value . '</option>';
							}
							else if (! empty($value) && $current_value == $value)
							{
								$html .= '<input type="radio" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $current_key . '" checked>' . $current_value . '</option>';
							}
							else
							{
								$html .= '<input type="radio" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" value="' . $current_key . '">' . $current_value . '</option>';
							}
						}
					}

					if ($field['type'] == 'range')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . ' <span id="' . $field['form_name'] . '_helper">' . (! empty($value) ? $value : $field['default_value']) . '</span></label>
							<input type="range" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '" min="' . $field['min_value'] . '" max="' . $field['max_value'] . '" value="' . (! empty($value) ? $value : $field['default_value']) . '">
						';
					}

					if ($field['type'] == 'password')
					{
						$html .= '
							<label for="' . $field['form_name'] . '">' . $field['friendly_name'] . '</label>
							<input type="password" name="' . $field['form_name'] . '" id="' . $field['form_name'] . '">
						';
					}

					if ($validation_errors !== FALSE && isset($validation_errors[$field['form_name']]))
					{
						foreach ($validation_errors[$field['form_name']] as $error)
						{
							$html .= '<div class="form-error">* ' . $error . '</div>';
						}
					}
				}
			}

			if (isset($data_model['images_max']) && $data_model['images_max'] > 0)
			{
				$movie_id = $this->opus->urlargs->get_parameter('id');

				if (! $movie_id)
				{
					$movie_id = 'temp_' . uniqid(); //Temporary ID
					$_SESSION['temp_item_id'] = $movie_id;
				}

				$last_image_existed = TRUE;

				$html .= '<section id="image_upload_area" data-item-id="' . $movie_id . '" data-max-size="' . $data_model['images_max_size'] . '">';
					$html .= '<label for="image_upload_area">Images</label>';

						for ($x = 1; $x <= $data_model['images_max']; $x++)
						{
							$thumbs = glob($this->opus->config->path->image_upload . '/movies/' . $movie_id . '/' . $movie_id . '_' . $x . '.*');
							$thumbnail = (! empty($thumbs)) ? $this->opus->path_to_url($thumbs[0]) : $this->opus->path_to_url($this->opus->config->path->image_add);
							$class = ($last_image_existed === FALSE && empty($thumbs)) ? 'hidden' : 'image_upload';
							$last_image_existed = (! empty($thumbs)) ? TRUE : FALSE;

							$html .= '
								<section class="' . $class . '" id="images_upload_section_' . $x . '" data-image-id="' . $x . '">
									<img title="Upload image" class="images_upload_thumb" id="thumb_' . $x . '" src="' . $thumbnail . '" data-image-loading-url="' . $this->opus->path_to_url($this->opus->config->path->image_loading) . '" data-no-image-url="' .$this->opus->path_to_url($this->opus->config->path->image_add) . '">
									<a title="Remove image" class="remove-image-link" href="' . $this->opus->url['base'] . '/movies/image_remove/item_id=' . $movie_id . '/image_id=' . $x . '">
										<img src="' . $this->opus->url('assets/images/remove.png') . '">
									</a>
									<input type="file" id="images_file_upload_' . $x . '" class="images_upload_input" name="images_upload_input" accept="' . implode(', ', $data_model['images_accepted_types']) . '">
									<output class="images_upload_error" id="images_upload_error_' . $x . '"></output>
								</section>
							';
						}
				$html .= '</section>';
			}

			return $html;
		}

		private function to_date_format($input)
		{
			$timestamp = strtotime($input);
			return date('Y-m-d', $timestamp);
		}

		public function make_filters($data_model, $fields)
		{
			$html = '<form method="post" action="' . $this->opus->url('form/filter') . '">';

			foreach ($fields as $field => $filter_type)
			{
				switch ($filter_type)
				{
					case "checkboxes":
					{
						foreach ($data_model['fields'][$field]['fixed_values'] as $key => $value)
						{
							$html .= '<input type="checkbox" name="' . $key . '">' . $value . '<br>';
						}
					}
				}
			}

			$html .= '<input type="submit" value="Filter">';
			$html .= '</form>';

			return $html;
		}

		public function filter()
		{
			$url = "";

			foreach ($_POST as $key => $val)
			{
				$url .= $key . '/';
			}

			//Redirect
		}

		public function validate($data_model)
		{
			foreach ($data_model['fields'] as $field)
			{
				if (isset($_POST[$field['form_name']]))
				{
					$this_input = $_POST[$field['form_name']];
				
					if (($field['type'] == 'int' || $field['type'] == 'id') && ! is_numeric($this_input))
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' must be a numeric value.';
					}

					if (isset($field['min_length']) && strlen($this_input) < $field['min_length'])
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' must be more than ' . $field['min_length'] . ' characters!';
					}
					
					if (isset($field['max_length']) && strlen($this_input) >  $field['max_length'])
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' must be less than ' .  $field['max_length'] . ' characters!';
					}
					
					if ($field['type'] == 'email' && filter_var($this_input, FILTER_VALIDATE_EMAIL) === false)
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' is not a correctly formatted email address.';
					}

					if ($field['type'] == 'url' && filter_var($this_input, FILTER_VALIDATE_URL) === false)
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' is not a correctly formatted URL.';
					}

					if ($field['type'] == 'ip' && filter_var($this_input, FILTER_VALIDATE_IP) === false)
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' is not a correctly formatted IP address.';
					}
					
					if ($field['type'] == 'date' && ! strtotime($this_input))
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' is not a correctly formatted date.';
					}

					if ($field['type'] == 'date' && isset($field['max_date']) && strtotime($field['max_date']) < strtotime($this_input))
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' cannot be a later time than ' . $field['max_date'] . '.';
					}

					if ($field['type'] == 'date' && isset($field['min_date']) && strtotime($field['min_date']) > strtotime($this_input))
					{
						$error[$field['form_name']][] = $field['friendly_name'] . ' cannot be a later time than ' . $field['min_date'] . '.';
					}

					if ($field['type'] == 'password')
					{
						if (! preg_match("#.*^(?=.{8,50})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#", $this_input))
						{
							$error[$field['form_name']][] = $field['friendly_name'] . ' is not a strong password. It needs to be at least 8 characters long, and include 1) One uppercase character, 2) One lowercase character and 3) A number.';
						}
					}
					
					if (isset($field['exact_match']))
					{
						foreach ($field['exact_match'] as $match)
						{
							$matched = FALSE;

							if ($this_input === $match)
							{
								$matched = TRUE;
								break;
							}
						}
						
						if (! $matched)
						{
							$error[$field['form_name']][] = $field['friendly_name'] . ' did not match.';
						}
					}

					if (isset($field['fixed_values']))
					{
						if (! array_key_exists($this_input, $field['fixed_values']))
						{
							$error[$field['form_name']][] = $field['friendly_name'] . ' must be any of the following values: ' . implode(', ', $field['fixed_values']) . '.';
						}
					}
				}	
			}

			return (isset($error)) ? $error : NULL;
		}

		public function image_upload($file_path, $content)
		{
			$original_file_name = $_SERVER['HTTP_X_ORIGINAL_FILE_NAME'];
			$original_file_parts = explode('.', $original_file_name);
			$original_file_extension = strtolower(end($original_file_parts));
			$path = dirname($file_path);

			switch ($original_file_extension)
			{
				case 'png':
					$file_extension = 'png';
				case 'gif':
					$file_extension = 'gif';
				default:
					$file_extension = 'jpg';
			}

			if (! file_exists($path))
				mkdir($path);

			$file = $file_path . '.' . $file_extension;

			file_put_contents($file, file_get_contents($content));

			$this->opus->log->write('info', 'Uploaded ' . $file);

			return $this->opus->path_to_url($file);
		}

		public function save_temp_images($db_table, $new_id)
		{
			//Moving temporary uploaded images to the item IDs folder instead
			//We don't know the item ID before submitting

			if (! isset($_SESSION['temp_item_id']))
				return;

			$new_path = $this->opus->config->path->image_upload . $db_table . '/' . $new_id;

			if (! file_exists($new_path))
				mkdir($new_path);

			$old_id = $_SESSION['temp_item_id'];
			$old_path = $this->opus->config->path->image_upload . $db_table . '/' . $old_id;

			$counter = 1;

			foreach (glob($old_path . "\*") as $file)
			{
				$file_extension = end(explode('.', $file));
				$new_filename = $new_id . '_' . $counter . '.' . $file_extension;
				rename($file, $new_path . '/' . $new_filename);;

				$counter++;
			}

			rmdir($old_path);
		}

		public function image_remove($item_id, $image_id = FALSE)
		{
			//Removes images, to remove all images for an item, do not specify image_id

			if ($image_id === FALSE)
				$file_path = $this->opus->config->path->image_upload . 'movies/' . $item_id . '/*';
			else
				$file_path = $this->opus->config->path->image_upload . 'movies/' . $item_id . '/' . $item_id . '_' . $image_id . '.*';
	
			$file_array = glob($file_path);

			foreach ($file_array as $file)
				unlink($file);

			if ($image_id === FALSE)
				rmdir($this->opus->config->path->image_upload . 'movies/' . $item_id);

			return "OK";
		}

	}

?>