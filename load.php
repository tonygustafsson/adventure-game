<?php
	class load
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;
		}

		public function controller($area_name)
		{
			$controller_path = 'areas/' . $area_name . '/' . $area_name . '.controller.php';

			if (file_exists($controller_path))
			{
				//Specific controller, incl default
				require_once($controller_path);
				$class_name = $this->opus->url['area'] . '_controller';
				$controller = new $class_name();
				$method_name = $this->opus->url['method'];
				
				if (method_exists($controller, $method_name))
				{
					$controller->$method_name();
				}
				else
				{
					//Page does not exist, get index instead
					$controller->index();
				}

				return true;
			}
			else
			{
				return false;
			}
		}

		public function module($module)
		{
			$this_module_path = 'modules/' . $module . '/' . $module . '.module.php';

			if (file_exists($this_module_path))
			{
				require_once($this_module_path);
				$class_name = $module . '_module';
				return new $class_name();
			}
		}

		public function view($view, $data = FALSE, $load_as_variable = FALSE)
		{
			$debug_backtrace = debug_backtrace();
			$current_path = dirname($debug_backtrace[0]['file']);

			//Check if view file exists in the current folder, if not - check /views
			if (file_exists($current_path . '/' . $view . '.view.php'))
				$view_path = $current_path . '/' . $view . '.view.php';
			else if (file_exists($this->opus->path['absolute'] . '/views/' . $view . '.view.php'))
				$view_path = $this->opus->path['absolute'] . '/views/' . $view . '.view.php';
			else
				throw new Exception("Could not find view '" . $view . "'.");

			//Extract data to variables, $data['test'] = $test
			if ($data)
				extract($data);

			if ($load_as_variable)
			{
				//Load the view	as a variable		
				ob_start();
				include($view_path); //Include view file
				$view_data = ob_get_contents();
				ob_end_clean();
				return $view_data;
			}
			else
			{
				include($view_path); //Include view file
			}
		}

		public function model($model)
		{
			$class_name = $model . '_model';

			//If the class already exists, do not load it again
			if (class_exists($class_name))
				return $class_name;

			//Get model path depending on if the caller method is a controller, a module or something else
			$backtrace = debug_backtrace()[0];

			if (isset($backtrace['file']) && strpos($backtrace['file'], '.controller.php') !== FALSE)
				$model_path = dirname($backtrace['file']) . '\\' . $model . '.model.php';
			else if (isset($backtrace['file']) && strpos($backtrace['file'], '.module.php') !== FALSE)
				$model_path = dirname($backtrace['file']) . '\\' . $model . '.model.php';
			else
				$model_path = $this->opus->path['absolute'] . '/models/' . $model . '.model.php';
					
			if (file_exists($model_path))
			{
				include($model_path);
				return new $class_name();
			}
			else
				echo 'Could not find model ' . $model_path;
		}

		public function css($css_files, $media = "all")
		{
			if (! is_array($css_files))
			{
				//Get a single CSS file
				return '<link rel="stylesheet" type="text/css" media="' . $media . '" href="' . $this->opus->path['relative'] . $this->opus->config->path->css . $css_files . '.css' . '">';
			}
			else
			{
				//Get more than one CSS file
				$output = "";
				
				foreach ($css_files as $css_file)
				{
					$output .= '<link rel="stylesheet" type="text/css" media="' . $media . '" href="' . $this->opus->path['relative'] . $this->opus->config->path->css . $css_file . '.css' . '">';
				}

				return $output;
			}
		}

		public function js($js_files)
		{
			if (! is_array($js_files))
			{
				//Get a single JS file
				return '<script type="text/javascript" src="' . $this->opus->path['relative'] . $this->opus->config->path->js . $js_files . '.js' . '"></script>';
			}
			else
			{
				//Get more than one JS file
				$output = "";
				
				foreach ($js_files as $js_file)
				{
					$output .= '<script type="text/javascript" src="' . $this->opus->path['relative'] . $this->opus->config->path->js . $js_file . '.js' . '"></script>';
				}

				return $output;
			}
		}

		public function url($url)
		{
			$url = ($url == "/") ? "" : $url;
			$url = $this->opus->url($url);

			header('Location: ' . $url);
			exit;
		}

		public function image($path)
		{
			//Checks if the image exists and delivers it, if not, deliver a default image
			$path = $this->opus->path['absolute'] . '/' . $path;

			if (file_exists($path))
				return $this->opus->path_to_url($path);
			else
				return $this->opus->path_to_url($this->opus->config->path->image_missing);
		}

		public function is_ajax_request()
		{
			return (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
		}

	}
?>