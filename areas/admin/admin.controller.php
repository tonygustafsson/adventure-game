<?php
	class admin_controller
	{
		public function __construct()
		{
			$this->opus =& opus::$instance;
		}

		public function index()
		{
			$view_data['page_title'] = 'Admin';
			$view_data['css'] = $this->opus->load->css(array('admin'));
			$view_data['js'] = $this->opus->load->js(array('admin'));
			
			$partial_data['room_files'] = glob("assets/json/rooms/*.json", GLOB_NOSORT);
			$view_data['partial'] = $this->opus->load->view('list-rooms', $partial_data, TRUE);
			
			$this->opus->load->view('admin-template', $view_data);
		}
		
		public function edit()
		{
			$room_file = "assets/json/rooms/" . $this->opus->url['path_parts'][2];
			
			if (!file_exists($room_file))
			{
				echo "File not found";
				exit;
			}
	
			$room_json_file_handle = fopen($room_file, "r") or die("Unable to open file!");
			$content = fread($room_json_file_handle, filesize($room_file));
			fclose($room_json_file_handle);
				
			$view_data['page_title'] = 'Edit room';
			$view_data['css'] = $this->opus->load->css(array('admin'));
			$view_data['js'] = $this->opus->load->js(array('admin'));
			
			$partial_data['room_name'] = $this->opus->url['path_parts'][2];
			$partial_data['room'] = json_decode($content);
			$view_data['partial'] = $this->opus->load->view('edit-room', $partial_data, TRUE);

			$this->opus->load->view('admin-template', $view_data);
		}
	}
?>