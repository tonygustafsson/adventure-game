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
			
			$room_data_file = "assets/json/rooms/beach-house.json";
			$room_data_file_handle = fopen($room_data_file, "r") or die("Unable to open file!");
			$view_data['room_data'] = fread($room_data_file_handle, filesize($room_data_file));
			fclose($room_data_file_handle);

			$view_data['partial'] = $this->opus->load->view('game', null, TRUE);
			
			$this->opus->load->view('game-template', $view_data);
		}
	}
?>