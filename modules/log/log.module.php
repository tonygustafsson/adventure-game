<?php
	class log_module
	{
		public function __construct()
		{	
			$this->opus =& opus::$instance;

			$this->url_accessible = array('view');
			
			//Create log path if it does not already exist
			if (! file_exists($this->opus->config->log->path))
				mkdir($this->opus->config->log->path);

			//Create new log file if does not already exist
			if (! file_exists($this->opus->config->log->path . $this->opus->config->log->current_file_name))
				$this->remove_old_log_files();
		}

		public function view()
		{
			$date = $this->opus->urlargs->get_parameter('date');
			$date_regex = '/^(19|20)\d\d[\-](0[1-9]|1[012])[\-](0[1-9]|[12][0-9]|3[01])$/'; //Y-m-d

			if ($date !== FALSE && preg_match($date_regex, $date))
			{
				//If there is a valid date in the URL, like /date=2014-10-26/, use this
				$date_parts = explode("-", $date);
				$date = mktime(12, 0, 0, $date_parts[1], $date_parts[2], $date_parts[0]);
			}
			else
				$date = time();

			$yesterday = $date - 86400;
			$tomorrow = $date + 86400;

			$log_data['log'] = $this->get_log($date);
			$log_data['title'] = 'Log entries from ' . date($this->opus->config->log->title_date_format, $date);
			$log_data['prev_link'] = $this->opus->url('log/view/date=' . date("Y-m-d", $yesterday));
			$log_data['current_link'] = $this->opus->url('log/view/date=' . date("Y-m-d"));
			$log_data['next_link'] = $this->opus->url('log/view/date=' . date("Y-m-d", $tomorrow));

			$view_data['page_title'] = 'View logs';
			$view_data['css'] = $this->opus->load->css(array('base', 'custom'));
			$view_data['partial'] = $this->opus->load->view('list', $log_data, TRUE);
			$this->opus->load->view('template', $view_data);
		}

		private function get_log($time = FALSE)
		{
			$output = array();

			if ($time === FALSE)
				$time = time();

			$file_date = date($this->opus->config->log->file_date_format, $time);
			$log_file = $this->opus->config->log->path . $file_date . '.log';

			if (! file_exists($log_file))
				return $output;

			$fp = fopen($log_file, 'r');
			$content = fread($fp, filesize($log_file));
			fclose($fp);

			$content = explode("\r\n", $content);

			for ($x = 0; $x < count($content); $x++)
			{
				if (empty($content[$x]))
					continue;

				$this_row = explode("\t", $content[$x]);
				$current_content['time'] = $this_row[0];
				$current_content['source'] = $this_row[1];
				$current_content['level'] = $this_row[2];
				$current_content['message'] = $this_row[3];

				$output[] = $current_content;
			}

			return $output;
		}

		public function write($level, $message)
		{
			$log_level = 4; //Debug default

			switch (strtolower($level))
			{
				case "critical":
					$log_level = 1;
				case "warning":
					$log_level = 2;
				case "info":
					$log_level = 3;
			}

			if ($log_level <= $this->opus->config->log->level)
			{
				$trace = debug_backtrace();
				$topic = $trace[1]['class'];

				$message = date($this->opus->config->log->time_format) . "\t" . $topic . "\t" . $level . "\t" . $message . "\r\n";

				$fp = fopen($this->opus->config->log->path . $this->opus->config->log->current_file_name, $this->opus->config->log->file_method);
				fwrite($fp, $message);
				fclose($fp);
			}
		}

		private function remove_old_log_files()
		{
			$this->write('info', 'Removing old log files, if any...');

			$log_files = glob($this->opus->config->log->path . '/*', GLOB_NOSORT | GLOB_BRACE);
			$delete_older_than = strtotime("-" . $this->opus->config->log->history_keep . " days");

			foreach($log_files as $log_file) {
				if (filectime($log_file) < $delete_older_than)
				{
					$this->write('info', 'Removing old log file ' . $log_file . ', created at ' . date("Ymd H:i:s", filectime($log_file)));
					unlink($log_file);
				}
			}
		}

	}
?>