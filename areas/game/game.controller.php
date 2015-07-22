<?php
	class game_controller
	{
		public function __construct()
		{
			$this->opus =& opus::$instance;
		}

		public function index()
		{
			$view_data['page_title'] = 'Adventure Game';
			$view_data['page_description'] = "An experiment.";
			$view_data['page_keywords'] = "game, adventure";
			$view_data['css'] = $this->opus->load->css(array('game'));
			$view_data['js'] = $this->opus->load->js(array('game'));
			
			$room = 'beach-house';
			
			$room_data_file = "assets/json/rooms/" . $room . ".json";
			$room_data_file_handle = fopen($room_data_file, "r") or die("Unable to open file!");
			$view_data['room_data'] = fread($room_data_file_handle, filesize($room_data_file));
			fclose($room_data_file_handle);

			$partial_data['room_data'] = json_decode($view_data['room_data']);
			$partial_data['room_images_root'] = $this->opus->url('assets/images/items/' . $room);
			$view_data['partial'] = $this->opus->load->view('game', $partial_data, TRUE);
			
			$this->opus->load->view('game-template', $view_data);
		}
	}
?>