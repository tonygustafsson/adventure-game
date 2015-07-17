<?php
	class cache_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;

			$this->cache_key = $this->opus->url['area'] . '/' . $this->opus->url['method'];
			$this->cache_file = $this->opus->path['absolute'] . "/cache/" . $this->opus->url['area'] . '_' . $this->opus->url['method'] . '.html';
			$this->cache_file_modified = (array_key_exists($this->cache_key, $this->opus->config->cache->pages) && file_exists($this->cache_file)) ? filemtime($this->cache_file) : 0;
			$this->cache_max_time = (array_key_exists($this->cache_key, $this->opus->config->cache->pages)) ? $this->opus->config->cache->pages[$this->cache_key] : 0;

			$this->has_cache_enabled = $this->has_cache_enabled();
			$this->has_fresh_cache_file = $this->has_fresh_cache_file();

			if ($this->has_cache_enabled && $this->has_fresh_cache_file)
			{
				//Prevent normal flow and deliver cache instead
				$this->opus->prevent_controller_load = $this->has_cache_enabled;

				echo '<p>From cache: ' . $this->cache_file . '</p>';
				include($this->cache_file);
			}
			else if ($this->has_cache_enabled)
			{
				//Should be cached but no fresch cache file
				$this->opus->ending_task['cache'] = 'save_cache_file';
			}
		}

		public function has_cache_enabled()
		{
			return array_key_exists($this->cache_key, $this->opus->config->cache->pages);
		}

		public function has_fresh_cache_file()
		{
			if (file_exists($this->cache_file) && time() < ($this->cache_file_modified + $this->cache_max_time))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}

		public function save_cache_file()
		{
			$this->opus->log->write('info', 'Saved cache: ' . $this->cache_file);

			$contents = ob_get_contents();

			$file = fopen($this->cache_file, "w");
			fwrite($file, $contents);
			fclose($file);
		}

	}
?>