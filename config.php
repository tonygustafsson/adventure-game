<?php
	class config
	{
		public function __construct()
		{
			$this->opus =& opus::$instance;

			$this->base_url = '/labs/adventure-game/'; //Just '/' if root
			$this->site_name = 'Adventure Game';
			$this->site_email = 'adventure.game@test.com';
			$this->debug = FALSE;

			//Preload modules that can interfere with normal work flow. auth and cache needs this to prevent controller loading
			$this->pre_load_modules = array();

			/* Set up routing config */
			$this->routing = new routing_config;

			/* Set up path config */
			$this->path = new path_config;

			/* LOAD MODULE CONFIGURATION */
			// These can be commented out if you will not use these
			//$this->auth = new auth_config;
			//$this->cache = new cache_config;
			//$this->database = new database_config;
			//$this->email = new email_config;
			$this->form = new form_config;
			//$this->instagram = new instagram_config;
			$this->log = new log_config;
			//$this->pagination = new pagination_config;
			//$this->session = new session_config;
			$this->urlargs = new urlargs_config;
			//$this->xml = new xml_config;
		}
	}

	class path_config
	{
		public function __construct()
		{
			$this->opus =& opus::$instance;

			$this->css = '/assets/css/';
			$this->js = '/assets/js/';

			$this->image_upload = $this->opus->path['absolute'] . '/assets/images/uploads/';
			$this->image_missing = $this->opus->path['absolute'] . '/assets/images/image_missing.png';
			$this->image_loading = $this->opus->path['absolute'] . '/assets/images/image_loading.png';
			$this->image_add = $this->opus->path['absolute'] . '/assets/images/image_add.png';
		}
	}

	class routing_config
	{
		public function __construct()
		{
			$this->routes = array(
				'default' => 'game',
				'404' =>	'404',
				'sitemap.xml' => 'xml/sitemap',
				'sitemap.xml.gz' => 'xml/sitemap_gzip'
			);
		}
	}

	class auth_config
	{
		public function __construct()
		{
			$this->auto_load = TRUE;
			$this->auto_route = TRUE;

			//If TRUE, all pages will be password protected by default
			$this->protect_all = TRUE;

			//Overwrites $this->protect_all for specific pages
			$this->restricted['movies/index'] = FALSE;
			$this->restricted['movies/sort'] = FALSE;
			$this->restricted['movies/search'] = FALSE;
			$this->restricted['form/filter'] = FALSE;
			$this->restricted['xml/sitemap'] = FALSE;

			//If true, the user has to activate his user via email
			$this->require_user_activation = TRUE;
		}
	}

	class cache_config
	{
		public function __construct()
		{
			$this->auto_load = TRUE;
			$this->auto_route = FALSE;

			$this->pages['movies/index'] = 10;
		}
	}

	class database_config
	{
		public function __construct()
		{
			$this->auto_load = TRUE;
			$this->auto_route = FALSE;

			$this->host = 'localhost';
			$this->username = 'root';
			$this->password = '';
			$this->database = 'opusframe';
		}
	}

	class email_config
	{
		public function __construct()
		{
			$this->auto_load = TRUE;
			$this->auto_route = FALSE;

			$this->enable_emailing  = FALSE; //If FALSE, only log to file for debugging

			$this->smtp_host		= "localhost";
			$this->smtp_port		= 25; //25 for SMTP, 587 for SMTP through SSL
			$this->smtp_auth		= FALSE;
			$this->smtp_user		= "";
			$this->smtp_pass		= "";
			$this->smtp_timeout		= 5; //Seconds
			$this->command_retry_timeout = 0.1 * 1000000; //Microseconds
			$this->command_retries	= 20; //command_retry_timeout times this number makes the total timeout for commands
			$this->charset			= "utf-8";
			$this->html_format 		= TRUE;
		}
	}

	class form_config
	{
		public function __construct()
		{
			$this->auto_load = TRUE;
			$this->auto_route = FALSE;
		}
	}

	class instagram_config
	{
		public function __construct()
		{
			$this->auto_load = TRUE;
			$this->auto_route = TRUE;

			$this->opus =& opus::$instance;

			$this->image_path = $this->opus->path['absolute'] . "/assets/images/instagram/";
			$this->user_id = '1336819';
			$this->no_instagram_images = 20; //From Instagram API, Max 33
			$this->client_id = '3df4102f06814637b7f660639b409fa0';
			$this->media_url = 'https://api.instagram.com/v1/users/' . $this->user_id . '/media/recent?client_id=' . $this->client_id . '&count=' . $this->no_instagram_images;
		
			$this->save_small_images = TRUE;
			$this->save_medium_images = TRUE;
			$this->save_large_images = TRUE;

			$this->no_images = 10; //Images to get from cache
		}
	}

	class log_config
	{
		public function __construct()
		{
			$this->auto_load = TRUE;
			$this->auto_route = TRUE;

			$this->opus =& opus::$instance;

			//Log level 0: Nothing is logged
			//Log level 1: CRITICAL, errors that breaks the page
			//Log level 2: WARNING, errors that impact users experience
			//Log Level 3: INFO, not very important, but nice to know
			//Log Level 4: DEBUG, more than you want to know in a production environement :)
			$this->level = 4;

			$this->path = $this->opus->path['absolute'] . '/logs/';
			$this->title_date_format = "Y-m-d"; //For title in log viewer
			$this->file_date_format = "Ymd";
			$this->current_file_name = date($this->file_date_format) . '.log';
			$this->time_format = "H:i:s";
			$this->file_method = "a"; //a = Open for writing only, pointer at the end of the file. Create if not exists.
			$this->history_keep = 90; //Keep log files newer than # days
		}
	}

	class pagination_config
	{
		public function __construct()
		{
			$this->auto_load = TRUE;
			$this->auto_route = FALSE;

			$this->items_per_page = 5;
			$this->start_string = '&laquo;Start';
			$this->prev_string = '&lt;Prev';
			$this->next_string = 'Next&gt;';
			$this->last_string = 'Last&raquo;';
		}
	}

	class session_config
	{
		public function __construct()
		{
			$this->auto_load = TRUE;
			$this->auto_route = FALSE;

			$this->timeout = 30 * 60; //Seconds
			$this->created_identifier = 'session_created';
			$this->flash_pre_name = 'flash_';
			$this->flash_next_name = 'next_';
			$this->flash_current_name = 'current_';
		}
	}

	class urlargs_config
	{
		public function __construct()
		{
            $this->auto_load = TRUE;
            $this->auto_route = FALSE;
		}
	}

	class xml_config
	{
		public function __construct()
		{
			$this->auto_load = TRUE;
			$this->auto_route = TRUE;

            $this->rss_standard_priority = "0.5";
            $this->rss_standard_freq = "weekly";

            $this->rss_pages = array(
                    array('loc' => 'movies', 'changefreq' => 'daily', 'priority' => '1'),
                    array('loc' => 'movies/create')
            );
		}
	}

?>