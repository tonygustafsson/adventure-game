<?php
	class urlargs_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;

			$this->parameters = array();

			//Set correct parameters, the rest will be ignored
			$this->parameter_values['order'] = array('ASC', 'DESC');

			foreach ($this->opus->url['path_parts'] as $segment)
			{
				if (strpos($segment, '=') !== false)
				{
					//It's a URL parameter worth remembering
					$split_segment = explode('=', $segment);
					list($segment_key, $segment_value) = $split_segment;
					$this->parameters[$segment_key] = $segment_value;
				}
			}
		}

		public function get_parameter($key)
		{
			if (! array_key_exists($key, $this->parameters))
				return FALSE;

			$value = $this->parameters[$key];

			if (array_key_exists($key, $this->parameter_values) && ! in_array($value, $this->parameter_values[$key]))
				return FALSE;

			return $this->parameters[$key];
		}

		public function get_url($new_parameters = array())
		{
			$url_suffix = "";

			$parameters = array_merge($this->parameters, $new_parameters);

			foreach ($parameters as $key => $val)
			{
				$url_suffix .= '/' . $key . '=' . $val;
			}

			return $url_suffix;
		}

	}
?>