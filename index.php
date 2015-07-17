<?php
	class opus
	{
		public static $instance;

		public function __construct()
		{
			self::$instance =& $this;

			//Set up path info
			$this->path = $this->set_path_values();

			//Load the configuration
			require_once('config.php');
			$this->config = new config;

			//Set up URL Info
			$this->url = $this->set_url_values();

			//Load the loader (for ie controllers, models and views)
			require_once('load.php');
			$this->load = new load();

			$this->prevent_controller_load = FALSE; //An autoloaded module can prevent the loading of the controller
			$this->ending_task = FALSE; //A autoloaded module can set tasks to do in the destruct

			//Start output buffering so that modules can manipulate data
			ob_start();

			//Pre load modules
			foreach ($this->config->pre_load_modules as $module)
			{
				$this->$module = $this->load->module($module);
			}

			$area_name = $this->url['area'];
			$method_name = $this->url['method'];

			//Load modules if not already loaded with preloading
			if (! empty($area_name) && ! isset($this->$area_name) && isset($this->config->$area_name->auto_route) && $this->config->$area_name->auto_route === TRUE) {
				$this->$area_name = $this->load->module($area_name);
			}

			if ($this->prevent_controller_load === FALSE && $this->load->controller($this->url['area']))
			{
				//Run the controller if an autoloaded module does not prevent it through prevent_controller_load
				return;
			}
			else if (
				isset($this->$area_name)
				&& method_exists($this->$area_name, $method_name)
				&& isset($this->config->$area_name->auto_route)
				&& $this->config->$area_name->auto_route === TRUE
				&& isset($this->$area_name->url_accessible)
				&& in_array($method_name, $this->$area_name->url_accessible)
			)
			{
				//Load the method from the module if it exists and is accessible
				$this->$area_name->$method_name();
			}
			else if ($this->prevent_controller_load === FALSE)
			{
				//Page does not exist (no controller found, no module reroute found)
				header("HTTP/1.0 404 Not Found");

				$view_data['page_title'] = '404 - Not Found';
				$view_data['partial'] = $this->load->view('404', array(), TRUE);
				$this->load->view('game-template', $view_data);
			}
		}

		private function set_url_values()
		{
			$url_protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === TRUE ? 'https://' : 'http://';
			$url_requested = $url_protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$url = parse_url($url_requested);
			$url['full'] = $url_requested;
			$url['path'] = str_replace(substr($this->config->base_url, 0, -1), "", $url['path']);
			$url['path_parts'] = ($url['path'] != '/') ? explode('/', ltrim($url['path'], "/")) : array();
			$url['base'] = $url['scheme'] . '://' . $url['host'] . ((! empty($url['port']) ? ':' . $url['port'] : '') . rtrim($this->config->base_url, '/'));
			$url['relative'] = str_replace($url['base'], '', $url['full']);

			if (isset($url['query']))
			{
				$url['query'] = explode('&', $url['query']);

				foreach ($url['query'] as $key => $val)
				{
					$query_parts = explode('=', $val);
					$url['query'][$query_parts[0]] = $query_parts[1];
					unset($url['query'][$key]);
				}
			}

			if (isset($url['path_parts'][0]))
			{
				//Get first and second URL path if there is both, or else just the first one
				$router_match = (isset($url['path_parts'][1])) ? $url['path_parts'][0] . '/'  . $url['path_parts'][1] : $url['path_parts'][0];
				
				//Returns array with router values if there is a router match, or else return false
				$router_segments = (isset($this->config->routing->routes[$router_match])) ? explode("/", $this->config->routing->routes[$router_match]) : FALSE;
			}

			if (isset($router_segments) && $router_segments === FALSE)
			{
				//Not routed
				$url['area'] = (isset($url['path_parts'][0]) && ! strpos($url['path_parts'][0], '=')) ? $url['path_parts'][0] : $this->config->routing->routes['default'];
				$url['method'] = (isset($url['path_parts'][1]) && ! strpos($url['path_parts'][1], '=')) ? $url['path_parts'][1] : 'index';
			}
			else
			{
				//Routed
				$url['area'] = (isset($router_segments[0])) ? $router_segments[0] : $this->config->routing->routes['default'];
				$url['method'] = (isset($router_segments[1]) && ! strpos($router_segments[1], '=')) ? $router_segments[1] : 'index';
			}
		
			return $url;
		}

		private function set_path_values()
		{
			$path = pathinfo($_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF']);
			$path['absolute'] = $path['dirname'];
			$path_relative = pathinfo($_SERVER['PHP_SELF']);
			$path['relative'] = rtrim(rtrim($path_relative['dirname'], "/"), "\\");

			return $path;
		}

		public function url($url)
		{
			//Remove first / if it exists
			$url = ltrim($url, "/");

			return $this->url['base'] . '/' . $url;
		}

		public function path_to_url($path)
		{
			//Converts a physical path to a URL
			$url = str_replace($_SERVER['DOCUMENT_ROOT'], "", $path);

			return $url;
		}

		public function __destruct()
		{
			if ($this->ending_task)
			{
				foreach ($this->ending_task as $module => $task)
				{
					$this->$module->$task();
				}
			}

			//Output the content to the browser
			ob_end_flush();
			
			if ($this->config->debug)
			{
				echo '<pre id="debug">';
					echo '<h2>Debug info</h2>';
					echo '<h3>$this</h3>';
					print_r($this);
					echo '<h3>$_GET</h3>';
					print_r($_GET);
					echo '<h3>$_POST</h3>';
					print_r($_POST);
					echo '<h3>$_SESSION</h3>';
					print_r($_SESSION);
					echo '<h3>$_SERVER</h3>';
					print_r($_SERVER);
				echo '</pre>';
			}
		}

		public function __get($module)
		{
			//Dynamically load modules with $this->opus->module_name->method_name();
			if (! isset($this->config->$module) || $this->config->$module->auto_load !== TRUE)
				throw new Exception("Module '" . $module . "' was called but is not set to load automatically.");

			$module_path = 'modules/' . $module . '/' . $module . '.module.php';
			$class_name = $module . '_module';

			//Do not load a module twice
			if (class_exists($class_name))
				return;

			if (file_exists($module_path))
			{
				include_once($module_path);
				$this->$module = new $class_name;

				return $this->$module;
			}

		}

	}

	//Run the framework
	$opus = new opus;
?>