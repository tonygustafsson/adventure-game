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
			$view_data['js'] = $this->opus->load->js(array('items', 'game'));
			$view_data['partial'] = $this->opus->load->view('game', null, TRUE);
			
			$this->opus->load->view('game-template', $view_data);
		}
	}
?>