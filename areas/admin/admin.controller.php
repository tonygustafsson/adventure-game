<?php
	class admin_controller
	{
		public function __construct()
		{
			$this->opus =& opus::$instance;
			$this->model = $this->opus->load->model('room');
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
		
		public function create()
		{
			$view_data['page_title'] = 'Create room';
			$view_data['css'] = $this->opus->load->css(array('admin'));
			$view_data['js'] = $this->opus->load->js(array('admin'));
			
			$partial_data['model'] = $this->model;
			$view_data['partial'] = $this->opus->load->view('create-room', $partial_data, TRUE);
			$this->opus->load->view('admin-template', $view_data);
		}
		
		public function edit()
		{
			$room_file_name = $this->opus->url['path_parts'][2];
			$room_file = "assets/json/rooms/" . $room_file_name . '.json';
			
			$room_json_file_handle = fopen($room_file, "r") or die("Unable to open file!");
			$content = fread($room_json_file_handle, filesize($room_file));
			fclose($room_json_file_handle);
				
			$view_data['page_title'] = 'Edit room';
			$view_data['css'] = $this->opus->load->css(array('admin'));
			$view_data['js'] = $this->opus->load->js(array('admin'));
			
			$partial_data['room_name'] = $room_file_name;
			$partial_data['room_data'] = json_decode($content);
			$partial_data['model'] = (object)$this->model;
			$view_data['partial'] = $this->opus->load->view('edit-room', $partial_data, TRUE);

			$this->opus->load->view('admin-template', $view_data);
		}
		
		public function save_room()
		{
			$output = array();
			
			foreach ($_POST as $key => $items)
			{
				if (!is_array($items))
					continue;
				
				// Loop through the post and structure an array with the items as keys
				foreach ($items as $item_counter => $value)
				{
					if (isset($_POST['id'][$item_counter]))
					{
						// Stupid form turns true to 1 and false to 0
						if ($value == "true")
							$value = true;
						else if ($value == "false")
							$value = false;
						
						$output['items'][$_POST['id'][$item_counter]][$key] = $value;
					}
					else
					{
						// Room doesn't have an ID
						$output[$key] = $value;
					}
				}
			}
			
			ksort($output);
			
			$output = json_encode($output, JSON_PRETTY_PRINT);
			
			$room_file = "assets/json/rooms/" . $_POST['room_name'] . ".json";
			$room_json_file_handle = fopen($room_file, "w") or die("Unable to open file!");
			$content = fwrite($room_json_file_handle, $output);
			fclose($room_json_file_handle);
			
			$this->opus->load->url('/admin');
		}
	}
?>